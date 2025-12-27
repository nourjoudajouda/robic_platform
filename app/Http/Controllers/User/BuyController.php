<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;
use App\Models\Asset;
use App\Models\Batch;
use App\Models\ChargeLimit;
use App\Models\GatewayCurrency;
use App\Models\BeanHistory;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    /**
     * حساب السعر المتوسط والكميات من عدة sell orders بناءً على الكمية المطلوبة
     * يستخدم نفس منطق JavaScript: حساب سعر السوق (المتوسط الموزون) وإعادة حسابه بعد كل كمية
     */
    private function calculatePriceFromMultipleOrders($productId, $requestedQuantity)
    {
        // جلب جميع sell orders مرتبة حسب السعر (أرخص أولاً)
        $batchOrders = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->get();
        
        $userOrders = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->get();
        
        // دمج جميع الأوامر في مصفوفة واحدة وترتيبها حسب السعر
        $orderBook = [];
        foreach ($batchOrders as $order) {
            $orderBook[] = [
                'type' => 'batch',
                'id' => $order->id,
                'price' => (float)$order->sell_price,
                'qty' => (float)($order->available_quantity ?? $order->quantity),
            ];
        }
        foreach ($userOrders as $order) {
            $orderBook[] = [
                'type' => 'user',
                'id' => $order->id,
                'price' => (float)$order->sell_price,
                'qty' => (float)($order->available_quantity ?? $order->quantity),
            ];
        }
        
        // ترتيب حسب السعر
        usort($orderBook, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        if (empty($orderBook)) {
            return [
                'success' => false,
                'message' => 'No available quantity',
                'available_quantity' => 0,
                'average_price' => 0,
                'total_amount' => 0,
                'orders' => [],
                'first_price' => 0,
                'pending_quantity' => $requestedQuantity,
                'total_market_quantity' => 0,
            ];
        }
        
        // حساب الكمية الكلية المتوفرة في السوق
        $totalMarketQuantity = array_sum(array_column($orderBook, 'qty'));
        
        // أرخص سعر متوفر
        $cheapestPrice = $orderBook[0]['price'];
        
        // حساب الكمية المتوفرة بأرخص سعر فقط
        $quantityAtCheapestPrice = 0;
        foreach ($orderBook as $order) {
            if (abs($order['price'] - $cheapestPrice) < 0.01) { // نفس السعر تقريباً
                $quantityAtCheapestPrice += $order['qty'];
            } else {
                break; // توقف عند أول سعر مختلف
            }
        }
        
        $remainingQuantity = $requestedQuantity;
        $totalAmount = 0;
        $ordersToBuy = [];
        $fulfilledQuantity = 0;
        $pendingQuantity = 0;
        
        // نشتري فقط من أرخص سعر
        if ($quantityAtCheapestPrice >= $requestedQuantity) {
            // الكمية المطلوبة متوفرة بالكامل بأرخص سعر
            foreach ($orderBook as $order) {
                if (abs($order['price'] - $cheapestPrice) < 0.01 && $remainingQuantity > 0) {
                    $qtyToTake = min($remainingQuantity, $order['qty']);
                    
                    $ordersToBuy[] = [
                        'type' => $order['type'],
                        'order_id' => $order['id'],
                        'quantity' => $qtyToTake,
                        'price' => $cheapestPrice,
                    ];
                    
                    $totalAmount += $qtyToTake * $cheapestPrice;
                    $remainingQuantity -= $qtyToTake;
                    $fulfilledQuantity += $qtyToTake;
                }
            }
            
            return [
                'success' => true,
                'average_price' => $cheapestPrice,
                'total_amount' => $totalAmount,
                'orders' => $ordersToBuy,
                'price_changes' => [],
                'first_price' => $cheapestPrice,
                'fulfilled_quantity' => $fulfilledQuantity,
                'pending_quantity' => 0,
                'total_market_quantity' => $totalMarketQuantity,
            ];
        } else {
            // الكمية المطلوبة أكبر من المتوفر بأرخص سعر
            // نشتري كل ما هو متوفر بأرخص سعر
            foreach ($orderBook as $order) {
                if (abs($order['price'] - $cheapestPrice) < 0.01) {
                    $qtyToTake = $order['qty'];
                    
                    $ordersToBuy[] = [
                        'type' => $order['type'],
                        'order_id' => $order['id'],
                        'quantity' => $qtyToTake,
                        'price' => $cheapestPrice,
                    ];
                    
                    $totalAmount += $qtyToTake * $cheapestPrice;
                    $fulfilledQuantity += $qtyToTake;
                }
            }
            
            $pendingQuantity = $requestedQuantity - $fulfilledQuantity;
            
            return [
                'success' => false,
                'message' => 'Insufficient quantity at lowest price',
                'available_quantity' => $fulfilledQuantity,
                'average_price' => $cheapestPrice,
                'total_amount' => $totalAmount,
                'orders' => $ordersToBuy,
                'first_price' => $cheapestPrice,
                'pending_quantity' => $pendingQuantity,
                'pending_price' => $cheapestPrice,
                'fulfilled_quantity' => $fulfilledQuantity,
                'total_market_quantity' => $totalMarketQuantity,
            ];
        }
        
        return [
            'success' => true,
            'average_price' => $cheapestPrice,
            'total_amount' => $totalAmount,
            'orders' => $ordersToBuy,
            'price_changes' => [],
            'first_price' => $cheapestPrice,
        ];
    }

    public function buyForm()
    {
        $pageTitle = 'Buy Green Coffee';
        
        // البدء من جدول المنتجات - جلب جميع المنتجات النشطة
        $products = \App\Models\Product::where('status', Status::ENABLE)
            ->with(['unit', 'currency'])
            ->get();
        
        $productsWithSellOrders = [];
        
        // لكل منتج، البحث عن batches فعالة مرتبطة بـ batch_sell_orders نشطة
        foreach ($products as $product) {
            $productId = $product->id;
            $hasSellOrders = false;
            
            // جلب batches فعالة لهذا المنتج والتي لديها batch_sell_orders نشطة
            $batches = Batch::where('product_id', $productId)
                ->where('status', Status::ENABLE)
                ->whereHas('sellOrders', function($query) {
                    $query->where('status', Status::SELL_ORDER_ACTIVE)
                        ->where(function($q) {
                            $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                              ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                        });
                })
                ->with(['sellOrders' => function($query) {
                    $query->where('status', Status::SELL_ORDER_ACTIVE)
                        ->where(function($q) {
                            $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                              ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                        })
                        ->orderBy('sell_price', 'asc');
                }])
                ->get();
            
            // جمع batch_sell_orders من batches
            $batchSellOrders = [];
            $productBatches = [];
            foreach ($batches as $batch) {
                foreach ($batch->sellOrders as $sellOrder) {
                    $batchSellOrders[] = [
                        'type' => 'batch',
                        'order' => $sellOrder,
                        'sell_price' => $sellOrder->sell_price,
                        'available_quantity' => $sellOrder->available_quantity ?? $sellOrder->quantity,
                    ];
                    $hasSellOrders = true;
                }
                // إضافة batch للمنتج
                $productBatches[] = $batch;
            }
            
            // جلب user_sell_orders نشطة لهذا المنتج
            $userSellOrders = \App\Models\UserSellOrder::where('product_id', $productId)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->where(function($q) {
                    $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                      ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                })
                ->with(['batch', 'warehouse'])
                ->orderBy('sell_price', 'asc')
                ->get();
            
            // جمع user_sell_orders
            $userSellOrdersArray = [];
            foreach ($userSellOrders as $sellOrder) {
                $userSellOrdersArray[] = [
                    'type' => 'user',
                    'order' => $sellOrder,
                    'sell_price' => $sellOrder->sell_price,
                    'available_quantity' => $sellOrder->available_quantity ?? $sellOrder->quantity,
                ];
                $hasSellOrders = true;
                
                // إضافة batch من user_sell_order إذا كان موجوداً ومرتبطاً بنفس المنتج
                if ($sellOrder->batch && $sellOrder->batch->product_id == $productId) {
                    $existingBatchIds = array_map(function($b) { 
                        return is_object($b) ? ($b->id ?? null) : null; 
                    }, $productBatches);
                    if (!in_array($sellOrder->batch->id, $existingBatchIds)) {
                        $productBatches[] = $sellOrder->batch;
                    }
                }
            }
            
            // إذا كان المنتج لديه sell orders، أضفه للقائمة
            if ($hasSellOrders) {
                // دمج batch_sell_orders و user_sell_orders
                $allSellOrders = array_merge($batchSellOrders, $userSellOrdersArray);
                
                // ترتيب حسب السعر (أرخص سعر أولاً)
                usort($allSellOrders, function($a, $b) {
                    return $a['sell_price'] <=> $b['sell_price'];
                });
                
                $productsWithSellOrders[$productId] = [
                    'product' => $product,
                    'sell_orders' => $allSellOrders,
                    'batches' => $productBatches
                ];
            }
        }
        
        // حساب سعر السوق لكل منتج
        $marketPrices = [];
        foreach ($productsWithSellOrders as $productId => $productData) {
            $marketPrices[$productId] = Batch::calculateMarketPrice($productId);
        }
        // return $productData;

        return view('Template::user.buy.products', compact('pageTitle', 'productsWithSellOrders', 'marketPrices'));
    }
    
    public function buyFormWithProduct($productId)
    {
        $pageTitle = 'Buy Green Coffee';
        
        // جلب المنتج
        $product = \App\Models\Product::with('unit', 'currency')->findOrFail($productId);
        
        // حساب الكمية الإجمالية المتاحة من batch_sell_orders
        $batchSellOrdersTotal = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        // حساب الكمية الإجمالية المتاحة من user_sell_orders
        $userSellOrdersTotal = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        // الكمية الإجمالية المتاحة
        $totalAvailableQuantity = $batchSellOrdersTotal + $userSellOrdersTotal;
        
        // جلب أرخص sell order (من batch أو user)
        $cheapestBatchOrder = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->first();
        
        $cheapestUserOrder = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->first();
        
        // تحديد أرخص سعر
        $cheapestOrder = null;
        if ($cheapestBatchOrder && $cheapestUserOrder) {
            $cheapestOrder = $cheapestBatchOrder->sell_price <= $cheapestUserOrder->sell_price 
                ? ['type' => 'batch', 'order' => $cheapestBatchOrder, 'sell_order_id' => $cheapestBatchOrder->id, 'sell_price' => $cheapestBatchOrder->sell_price]
                : ['type' => 'user', 'order' => $cheapestUserOrder, 'user_sell_order_id' => $cheapestUserOrder->id, 'sell_price' => $cheapestUserOrder->sell_price];
        } elseif ($cheapestBatchOrder) {
            $cheapestOrder = ['type' => 'batch', 'order' => $cheapestBatchOrder, 'sell_order_id' => $cheapestBatchOrder->id, 'sell_price' => $cheapestBatchOrder->sell_price];
        } elseif ($cheapestUserOrder) {
            $cheapestOrder = ['type' => 'user', 'order' => $cheapestUserOrder, 'user_sell_order_id' => $cheapestUserOrder->id, 'sell_price' => $cheapestUserOrder->sell_price];
        }
        
        // حساب سعر السوق
        $marketPrice = Batch::calculateMarketPrice($productId);
        
        // جلب معلومات إضافية من batches (درجة الجودة وبلد المنشأ)
        $batches = Batch::where('product_id', $productId)
            ->where('status', Status::ENABLE)
            ->whereHas('sellOrders', function($query) {
                $query->where('status', Status::SELL_ORDER_ACTIVE)
                      ->where(function($q) {
                          $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                            ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                      });
            })
            ->get();
        
        $qualityGrade = $batches->first()->quality_grade ?? null;
        $originCountry = $batches->first()->origin_country ?? null;
        
        // جلب جميع sell orders مرتبة حسب السعر لإرسالها للواجهة
        $allSellOrders = [];
        $batchOrders = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->get();
        
        foreach ($batchOrders as $order) {
            $allSellOrders[] = [
                'type' => 'batch',
                'id' => $order->id,
                'sell_price' => $order->sell_price,
                'available_quantity' => $order->available_quantity ?? $order->quantity,
            ];
        }
        
        $userOrders = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->orderBy('sell_price', 'asc')
            ->get();
        
        foreach ($userOrders as $order) {
            $allSellOrders[] = [
                'type' => 'user',
                'id' => $order->id,
                'sell_price' => $order->sell_price,
                'available_quantity' => $order->available_quantity ?? $order->quantity,
            ];
        }
        
        // ترتيب حسب السعر
        usort($allSellOrders, function($a, $b) {
            return $a['sell_price'] <=> $b['sell_price'];
        });
        
        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();
        
        return view('Template::user.buy.product_form', compact('pageTitle', 'product', 'totalAvailableQuantity', 'cheapestOrder', 'marketPrice', 'chargeLimit', 'qualityGrade', 'originCountry', 'allSellOrders'));
    }
    
    /**
     * إنشاء طلب معلق
     */
    public function createPendingOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|numeric|gt:0',
            'requested_price' => 'required|numeric|gt:0',
            'pending_quantity' => 'required|numeric|gt:0',
            'fulfilled_quantity' => 'nullable|numeric|gte:0',
        ]);
        
        $user = auth()->user();
        $product = \App\Models\Product::findOrFail($request->product_id);
        
        // لا نحتاج للتحقق من السعر لأن السعر هو سعر السوق (المتوسط الموزون)
        // وليس سعر order محدد
        
        $pendingOrder = new \App\Models\PendingBuyOrder();
        $pendingOrder->user_id = $user->id;
        $pendingOrder->product_id = $product->id;
        $pendingOrder->requested_quantity = $request->requested_quantity;
        $pendingOrder->requested_price = $request->requested_price;
        $pendingOrder->fulfilled_quantity = $request->fulfilled_quantity ?? 0;
        $pendingOrder->pending_quantity = $request->pending_quantity;
        $pendingOrder->order_code = \App\Models\PendingBuyOrder::generateOrderCode();
        $pendingOrder->status = Status::PENDING_BUY_ORDER;
        $pendingOrder->expires_at = now()->addDays(30); // انتهاء بعد 30 يوم
        $pendingOrder->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Pending order created successfully. You will be notified when the quantity becomes available.',
            'order_code' => $pendingOrder->order_code,
        ]);
    }
    
    /**
     * عرض الطلبات المعلقة
     */
    public function pendingOrders()
    {
        $pageTitle = 'Pending Buy Orders';
        $user = auth()->user();
        
        $pendingOrders = \App\Models\PendingBuyOrder::where('user_id', $user->id)
            ->where('status', Status::PENDING_BUY_ORDER)
            ->with('product.unit', 'product.currency')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());
        
        return view('Template::user.buy.pending_orders', compact('pageTitle', 'pendingOrders'));
    }
    
    /**
     * إلغاء طلب معلق
     */
    public function cancelPendingOrder($id)
    {
        $user = auth()->user();
        $pendingOrder = \App\Models\PendingBuyOrder::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', Status::PENDING_BUY_ORDER)
            ->firstOrFail();
        
        $pendingOrder->status = Status::PENDING_BUY_CANCELLED;
        $pendingOrder->save();
        
        $notify[] = ['success', 'Pending order cancelled successfully'];
        return back()->withNotify($notify);
    }
    
    public function buyFormWithBatch($batchId)
    {
        $pageTitle = 'Buy Green Coffee';
        
        // جلب جميع الباتشز المتاحة للاختيار (التي لديها sell orders نشطة)
        $batches = Batch::where('status', Status::ENABLE)
            ->whereHas('sellOrders', function($query) {
                $query->where('status', Status::SELL_ORDER_ACTIVE)
                      ->where(function($q) {
                          $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                            ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                      });
            })
            ->with([
                'product:id,name,status',
                'warehouse:id,name,location',
                'unit:id,name,symbol',
                'product.unit:id,name,symbol',
                'currency:id,name,symbol,code',
                'sellOrders' => function($query) {
                    $query->where('status', Status::SELL_ORDER_ACTIVE)
                          ->where(function($q) {
                              $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                                ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                          })
                          ->orderBy('sell_price', 'asc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // جلب الباتش المحدد (يجب أن يكون لديه sell orders نشطة)
        $selectedBatch = Batch::where('id', $batchId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->whereHas('sellOrders', function($query) {
                $query->where('status', Status::SELL_ORDER_ACTIVE)
                      ->where(function($q) {
                          $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                            ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                      });
            })
            ->with([
                'product.unit:id,name,symbol',
                'product.currency:id,name,symbol,code',
                'warehouse',
                'unit',
                'currency',
                'sellOrders' => function($query) {
                    $query->where('status', Status::SELL_ORDER_ACTIVE)
                          ->where(function($q) {
                              $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                                ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                          })
                          ->orderBy('sell_price', 'asc');
                }
            ])
            ->firstOrFail();
        
        // جلب أرخص batch_sell_order للباتش المحدد
        $cheapestSellOrder = $selectedBatch->sellOrders->first(); // تم ترتيبها بالفعل من أرخص سعر
        
        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();
        return view('Template::user.buy.form', compact('pageTitle', 'batches', 'selectedBatch', 'cheapestSellOrder', 'chargeLimit'));
    }

    public function buyStore(Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'product_id' => 'nullable|exists:products,id',
            'order_type' => 'nullable|in:batch,user',
            'sell_order_id' => 'nullable|integer',
            'amount'   => 'nullable|numeric|gt:0',
            'quantity' => 'nullable|numeric|gt:0',
            'action_type' => 'nullable|in:continue,pending', // نوع الإجراء: continue = إكمال الشراء، pending = تعليق الطلب
        ]);

        // يجب إدخال إما amount أو quantity
        if (!$request->amount && !$request->quantity) {
            $notify[] = ['error', 'Please enter either amount or quantity'];
            return back()->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();
        $sellOrder = null;
        $batch = null;
        $product = null;
        $availableQuantity = 0;
        $sellPrice = 0;

        // تحديد نوع الشراء (من batch_sell_order أو user_sell_order)
        if ($request->order_type == 'user' && $request->sell_order_id) {
            // الشراء من user_sell_order
            $sellOrder = \App\Models\UserSellOrder::where('id', $request->sell_order_id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->with(['product.unit', 'product.currency', 'asset', 'batch'])
                ->firstOrFail();
            
            $product = $sellOrder->product;
            $batch = $sellOrder->batch; // قد يكون null إذا كان من user
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            $sellPrice = $sellOrder->sell_price;
        } elseif ($request->product_id && $request->order_type == 'batch' && $request->sell_order_id) {
            // الشراء من batch_sell_order عبر product_id
            $product = \App\Models\Product::findOrFail($request->product_id);
            $sellOrder = \App\Models\BatchSellOrder::where('id', $request->sell_order_id)
                ->where('product_id', $product->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->with(['batch'])
                ->firstOrFail();
            
            $batch = $sellOrder->batch;
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            $sellPrice = $sellOrder->sell_price;
        } elseif ($request->batch_id) {
            // الشراء من batch_sell_order (الطريقة القديمة)
            $batch = Batch::where('id', $request->batch_id)
                ->where('status', Status::ENABLE)
                ->whereHas('sellOrders', function($query) {
                    $query->where('status', Status::SELL_ORDER_ACTIVE)
                          ->where(function($q) {
                              $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                                ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                          });
                })
                ->with([
                    'product',
                    'unit',
                    'currency',
                    'sellOrders' => function($query) {
                        $query->where('status', Status::SELL_ORDER_ACTIVE)
                              ->where(function($q) {
                                  $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                                    ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
                              })
                              ->orderBy('sell_price', 'asc');
                    }
                ])
                ->firstOrFail();

            // جلب أرخص batch_sell_order للباتش
            $sellOrder = $batch->sellOrders->first();
            
            if (!$sellOrder) {
                $notify[] = ['error', 'No sell orders available for this batch'];
                return back()->withNotify($notify);
            }
            
            $product = $batch->product;
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            $sellPrice = $sellOrder->sell_price;
        } else {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        // حساب الكمية والمبلغ - استخدام الدالة الجديدة لحساب السعر من عدة sell orders
        $quantity = 0;
        $amount = 0;
        
        if ($request->quantity) {
            $quantity = $request->quantity;
            // حساب السعر من عدة sell orders
            $priceCalculation = $this->calculatePriceFromMultipleOrders($product->id, $quantity);
            
            if (!$priceCalculation['success']) {
                $notify[] = ['error', 'The available quantity is ' . showAmount($priceCalculation['available_quantity'], 4, currencyFormat: false) . ' ' . ($product->unit->symbol ?? 'Unit')];
                return back()->withNotify($notify);
            }
            
            $amount = $priceCalculation['total_amount'];
        } else {
            $amount = $request->amount;
            // نحتاج لحساب الكمية من المبلغ - سنستخدم السعر المتوسط للسوق
            $marketPrice = Batch::calculateMarketPrice($product->id) ?? $sellPrice;
            $estimatedQuantity = $amount / $marketPrice;
            
            // حساب السعر الفعلي من عدة sell orders
            $priceCalculation = $this->calculatePriceFromMultipleOrders($product->id, $estimatedQuantity);
            
            if (!$priceCalculation['success']) {
                // إذا كانت الكمية المقدرة غير متوفرة، نجرب بكمية أقل
                $availableQty = $priceCalculation['available_quantity'];
                $priceCalculation = $this->calculatePriceFromMultipleOrders($product->id, $availableQty);
                if (!$priceCalculation['success']) {
                    $notify[] = ['error', 'Insufficient quantity available'];
                    return back()->withNotify($notify);
                }
                $quantity = $availableQty;
                $amount = $priceCalculation['total_amount'];
            } else {
                $quantity = $estimatedQuantity;
                // تعديل المبلغ بناءً على السعر الفعلي
                $amount = $priceCalculation['total_amount'];
            }
        }
        
        // حفظ معلومات الأوامر المتعددة في session
        $multipleOrders = $priceCalculation['orders'] ?? [];
        $priceChanges = $priceCalculation['price_changes'] ?? [];

        if ($chargeLimit->min_amount > $amount) {
            $notify[] = ['error', 'The minimum buy amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $amount) {
            $notify[] = ['error', 'The maximum buy amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $charge      = $chargeLimit->fixed_charge + $chargeLimit->percent_charge * $amount / 100;
        $vat         = $amount * $chargeLimit->vat / 100;
        $totalAmount = $amount + $charge + $vat;

        $buyData = [
            'order_type' => 'multiple', // نوع جديد للشراء من عدة orders
            'batch_id' => $batch ? $batch->id : null,
            'product_id' => $product->id,
            'sell_order_id' => $sellOrder->id, // للتوافق مع الكود القديم
            'amount' => $amount,
            'quantity' => getAmount($quantity, 8),
            'charge' => $charge,
            'vat' => $vat,
            'total_amount' => $totalAmount,
            'average_price' => $priceCalculation['average_price'] ?? $sellPrice,
            'multiple_orders' => $multipleOrders, // قائمة الأوامر المتعددة
            'price_changes' => $priceChanges, // تغييرات السعر
        ];

        session()->put('buy_data', (object) $buyData);

        return to_route('user.buy.payment.form');
    }

    public function paymentForm(Request $request)
    {
        $pageTitle = 'Buy Bean';
        $buyData   = session('buy_data');

        if (!$buyData) {
            $notify[] = ['error', 'Invalid session'];
            return redirect()->route('user.buy.form')->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();
        
        // جلب product مع unit لعرض الوحدة
        $product = \App\Models\Product::with('unit')->find($buyData->product_id);
        $batch = $buyData->batch_id ? Batch::with('product.unit')->find($buyData->batch_id) : null;

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();

        return view('Template::user.buy.payment_form', compact('pageTitle', 'gatewayCurrency', 'buyData', 'chargeLimit', 'batch', 'product'));
    }

    public function paymentSubmit(Request $request)
    {
        $buyData = session('buy_data');

        if (!$buyData) {
            $notify[] = ['error', 'Invalid session'];
            return redirect()->route('user.buy.form')->withNotify($notify);
        }

        $request->validate([
            'gateway'     => 'required',
            'currency'    => 'required',
        ]);

        if ($request->gateway != 'main') {
            $gate = GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

            if (!$gate) {
                $notify[] = ['error', 'Invalid gateway'];
                return back()->withNotify($notify);
            }
        }

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();

        $vat         = $buyData->amount * $chargeLimit->vat / 100;
        $charge      = $chargeLimit->fixed_charge + $chargeLimit->percent_charge * $buyData->amount / 100;
        $totalAmount = $buyData->amount + $charge + $vat;

        $user = auth()->user();
        $quantity = $buyData->quantity;
        $orderType = $buyData->order_type ?? 'batch';
        $sellOrderId = $buyData->sell_order_id ?? null;
        $multipleOrders = $buyData->multiple_orders ?? null;
        
        // إذا كان الشراء من عدة orders
        if ($orderType == 'multiple' && $multipleOrders) {
            $product = \App\Models\Product::findOrFail($buyData->product_id);
            
            if ($request->gateway == 'main') {
                if ($totalAmount > $user->balance) {
                    $notify[] = ['error', 'Insufficient balance'];
                    return back()->withNotify($notify);
                }
                
                $buyHistory = Asset::buyFromMultipleOrders(
                    $user, 
                    $buyData->product_id, 
                    $multipleOrders, 
                    $buyData->amount, // قيمة القهوة فقط (بدون charge/vat)
                    $quantity, 
                    $charge, 
                    $vat
                );
                
                $product = \App\Models\Product::find($buyData->product_id);
                $this->audit('buy', "تم شراء {$quantity} من المنتج: " . ($product->name_en ?? 'N/A'), $product, null, ['quantity' => $quantity, 'amount' => $buyData->amount, 'total' => $totalAmount]);
                
                $notify[] = ['success', 'Green Coffee purchased successfully'];
                return to_route('user.buy.success')->withNotify($notify)->with('buy_history', $buyHistory);
            }
        }
        
        if (!$sellOrderId) {
            $notify[] = ['error', 'Invalid sell order'];
            return back()->withNotify($notify);
        }

        // جلب sell order حسب النوع
        if ($orderType == 'user') {
            // الشراء من user_sell_order
            $userSellOrder = \App\Models\UserSellOrder::where('id', $sellOrderId)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->with(['product.unit', 'product.currency', 'asset', 'batch'])
                ->firstOrFail();
            
            $product = $userSellOrder->product;
            $availableQty = $userSellOrder->available_quantity ?? $userSellOrder->quantity;
            
            if ($quantity > $availableQty) {
                $notify[] = ['error', 'The available quantity is ' . showAmount($availableQty, 4, currencyFormat: false) . ' ' . ($product->unit->symbol ?? 'Unit')];
                return back()->withNotify($notify);
            }

            if ($request->gateway == 'main') {
                if ($totalAmount > $user->balance) {
                    $notify[] = ['error', 'Insufficient balance'];
                    return back()->withNotify($notify);
                }

                $buyHistory = Asset::buyFromUserSellOrder($user, $userSellOrder, $buyData->amount, $totalAmount, $quantity, $charge, $vat);

                $this->audit('buy', "تم شراء {$quantity} من المنتج: " . ($product->name_en ?? 'N/A'), $product, null, ['quantity' => $quantity, 'amount' => $buyData->amount, 'total' => $totalAmount]);

                $notify[] = ['success', 'Green Coffee purchased successfully'];
                return to_route('user.buy.success')->withNotify($notify)->with('buy_history', $buyHistory);
            }
        } else {
            // الشراء من batch_sell_order (الطريقة القديمة)
            $batch = Batch::where('id', $buyData->batch_id)
                ->where('status', Status::ENABLE)
                ->with(['product.unit', 'product.currency', 'unit', 'currency'])
                ->firstOrFail();
            
            $sellOrder = \App\Models\BatchSellOrder::where('id', $sellOrderId)
                ->where('batch_id', $batch->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->firstOrFail();
            
            $availableQty = $sellOrder->getAvailableQuantity();
            if ($quantity > $availableQty) {
                $notify[] = ['error', 'The available quantity is ' . showAmount($availableQty, 4, currencyFormat: false) . ' ' . ($batch->product->unit->symbol ?? 'Unit')];
                return back()->withNotify($notify);
            }

            if ($request->gateway == 'main') {
                if ($totalAmount > $user->balance) {
                    $notify[] = ['error', 'Insufficient balance'];
                    return back()->withNotify($notify);
                }

                $buyHistory = Asset::buyBean($user, $batch, $sellOrder, $buyData->amount, $totalAmount, $quantity, $charge, $vat);

                $this->audit('buy', "تم شراء {$quantity} من المنتج: " . ($batch->product->name_en ?? 'N/A'), $batch->product, null, ['quantity' => $quantity, 'amount' => $buyData->amount, 'total' => $totalAmount]);

                $notify[] = ['success', 'Green Coffee purchased successfully'];
                return to_route('user.buy.success')->withNotify($notify)->with('buy_history', $buyHistory);
            }
        }

        if ($gate->min_amount > $totalAmount || $gate->max_amount < $totalAmount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $buyInfo = [
            'data'  => [
                'amount'   => $buyData->amount,
                'quantity' => $quantity,
                'charge'   => $charge,
                'vat'      => $vat,
            ],
            'other' => [
                'order_type' => $orderType,
                'batch_id'   => $buyData->batch_id ?? null,
                'product_id' => $buyData->product_id ?? null,
                'sell_order_id' => $sellOrderId,
                'multiple_orders' => $multipleOrders ?? null,
                'success_url' => route('user.buy.success'),
                'failed_url'  => route('user.buy.form'),
            ],
        ];

        PaymentController::insertDeposit($gate, $totalAmount, $buyInfo);
        return to_route('user.deposit.confirm');
    }

    public function successPage()
    {
        $pageTitle  = 'Purchase Successful';
        
        // جلب آخر transaction للمستخدم (عملية الشراء)
        $lastTransaction = \App\Models\Transaction::where('user_id', auth()->id())
            ->where('remark', 'buy_bean')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$lastTransaction) {
            $notify[] = ['error', 'Invalid purchase data'];
            return to_route('user.buy.history')->withNotify($notify);
        }
        
        // جلب كل الـ buyHistories بنفس الـ trx (نفس عملية الشراء)
        $buyHistories = BeanHistory::buy()
            ->where('user_id', auth()->id())
            ->where('trx', $lastTransaction->trx)
            ->with('batch.product.unit', 'batch.product.currency', 'itemUnit', 'currency')
            ->get();
        
        if ($buyHistories->isEmpty()) {
            $notify[] = ['error', 'Invalid purchase data'];
            return to_route('user.buy.history')->withNotify($notify);
        }
        
        // حساب الإجماليات من كل الـ buyHistories (القيم المخزنة وقت الشراء)
        $totalQuantity = $buyHistories->sum('quantity');
        $totalAmount = $buyHistories->sum('amount'); // قيمة القهوة بدون رسوم
        $totalCharge = $buyHistories->sum('charge'); // مجموع الـ charge المخزنة
        $totalVat = $buyHistories->sum('vat'); // مجموع الـ VAT المخزنة
        
        // استخدام أول buyHistory للحصول على معلومات المنتج والوحدة
        $buyHistory = $buyHistories->first();
        
        // إرسال البيانات المجمعة (القيم المخزنة وقت الشراء - لا نعيد حساب شيء!)
        $purchaseData = [
            'quantity' => $totalQuantity,
            'amount' => $totalAmount, // Green Coffee Value (قيمة القهوة الأساسية)
            'charge' => $totalCharge, // الـ charge المخزن وقت الشراء
            'vat' => $totalVat, // الـ VAT المخزن وقت الشراء
            'total_amount' => $lastTransaction->amount, // Total Amount (من Transaction - المبلغ الكلي المخصوم)
            'unit_symbol' => $buyHistory->itemUnit->symbol ?? ($buyHistory->batch && $buyHistory->batch->product && $buyHistory->batch->product->unit ? $buyHistory->batch->product->unit->symbol : 'Unit'),
            'trx' => $lastTransaction->trx,
        ];
        
        return view('Template::user.buy.success', compact('pageTitle', 'buyHistory', 'purchaseData'));
    }

    public function history()
    {
        $pageTitle    = 'Buy History';
        $buyHistories = BeanHistory::buy()->where('user_id', auth()->id())
            ->with('batch.product.unit', 'batch.product.currency', 'itemUnit', 'currency')
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
        $vat          = $buyHistories->sum('vat');
        return view('Template::user.buy.history', compact('pageTitle', 'buyHistories', 'vat'));
    }

}
