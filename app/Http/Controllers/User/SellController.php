<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Batch;
use App\Models\ChargeLimit;
use App\Models\BeanHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SellController extends Controller
{
    public function sellForm()
    {
        $pageTitle   = "Sell Gold";
        $allAssets   = Asset::where('user_id', '=', auth()->id())
            ->where('quantity', '>', 0)
            ->with(['batch.product.unit', 'batch.product.currency', 'product.unit', 'product.currency'])
            ->get();
        
        // تجميع الـ assets حسب product_id
        $groupedAssets = $allAssets->groupBy('product_id')->map(function($productAssets) {
            $firstAsset = $productAssets->first();
            $product = $firstAsset->product ?? $firstAsset->batch->product;
            $productId = $firstAsset->product_id;
            
            // حساب الكمية الإجمالية
            $totalQuantity = $productAssets->sum('quantity');
            
            // حساب الكمية المستخدمة في sell orders نشطة لجميع assets هذا المنتج
            $usedQuantity = 0;
            foreach ($productAssets as $asset) {
                $usedQuantity += \App\Models\UserSellOrder::where('asset_id', $asset->id)
                    ->where('status', Status::SELL_ORDER_ACTIVE)
                    ->sum('quantity');
            }
            
            $availableQuantity = max(0, $totalQuantity - $usedQuantity);
            
            // حساب متوسط سعر الشراء من كل المشتريات للمنتج (شامل الرسوم والضريبة)
            $buyHistories = \App\Models\BeanHistory::where('user_id', auth()->id())
                ->where('type', Status::BUY_HISTORY)
                ->where('product_id', $productId)
                ->get();
            
            $totalCost = 0; // التكلفة الإجمالية (amount + charge + vat)
            $totalQty = 0;
            foreach ($buyHistories as $history) {
                if ($history->quantity > 0) {
                    // التكلفة الفعلية = قيمة القهوة + الرسوم + الضريبة
                    $cost = $history->amount + $history->charge + $history->vat;
                    $totalCost += $cost;
                    $totalQty += $history->quantity;
                }
            }
            
            // متوسط سعر الشراء الفعلي (شامل كل التكاليف)
            $averageBuyPrice = $totalQty > 0 ? ($totalCost / $totalQty) : ($productAssets->first()->buy_price ?? 0);
            
            // جلب آخر سعر سوق من market_price_history
            $latestMarketPrice = \App\Models\MarketPriceHistory::where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $currentMarketPrice = $latestMarketPrice ? $latestMarketPrice->market_price : 0;
            
            return (object)[
                'product_id' => $productId,
                'product' => $product,
                'total_quantity' => $totalQuantity,
                'available_quantity' => $availableQuantity,
                'average_buy_price' => $averageBuyPrice,
                'current_market_price' => $currentMarketPrice,
                'batches' => $productAssets, // كل الـ assets/batches للمنتج
                'batches_count' => $productAssets->count(),
            ];
        });
        
        $chargeLimit = ChargeLimit::where('slug', 'sell')->first();

        return view('Template::user.sell.form', compact('pageTitle', 'groupedAssets', 'chargeLimit'));
    }

    public function sellSubmit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'sell_price' => 'required|numeric|gt:0',
            'amount' => 'nullable|numeric|gt:0',
            'quantity' => 'nullable|numeric|gt:0',
        ]);

        // يجب إدخال إما amount أو quantity
        if (!$request->amount && !$request->quantity) {
            $notify[] = ['error', 'Please enter either amount or quantity'];
            return back()->withNotify($notify);
        }

        $user = auth()->user();
        $productId = $request->product_id;
        
        // جلب جميع assets للمنتج
        $assets = Asset::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->with('batch.product')
            ->get();
        
        if ($assets->isEmpty()) {
            $notify[] = ['error', 'No assets found for this product'];
            return back()->withNotify($notify);
        }
        
        $product = $assets->first()->product ?? $assets->first()->batch->product;
        $sellPrice = $request->sell_price;
        
        // حساب الكمية والمبلغ حسب نوع الإدخال
        if ($request->amount) {
            $amount = $request->amount;
            $quantity = $amount / $sellPrice;
        } else {
            $quantity = $request->quantity;
            $amount = $quantity * $sellPrice;
        }

        // حساب الكمية المتاحة الإجمالية للمنتج (بعد خصم الكميات المستخدمة في sell orders نشطة)
        $totalQuantity = $assets->sum('quantity');
        $totalUsedQuantity = 0;
        
        foreach ($assets as $asset) {
            $usedQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->sum('quantity');
            $totalUsedQuantity += $usedQuantity;
        }
        
        $availableQuantity = max(0, $totalQuantity - $totalUsedQuantity);

        if ($quantity > $availableQuantity) {
            $notify[] = ['error', 'The available quantity is ' . showAmount($availableQuantity, 4, currencyFormat: false) . ' ' . ($product->unit->symbol ?? 'Unit')];
            return back()->withNotify($notify);
        }

        if ($quantity <= 0) {
            $notify[] = ['error', 'Quantity must be greater than 0'];
            return back()->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'sell')->first();

        if ($chargeLimit->min_amount > $amount) {
            $notify[] = ['error', 'The minimum sell amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $amount) {
            $notify[] = ['error', 'The maximum sell amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $charge      = $chargeLimit->fixed_charge + $amount * $chargeLimit->percent_charge / 100;
        $finalAmount = $amount - $charge;

        // توزيع الكمية المطلوبة على assets متعددة (من الأكبر للأصغر حسب الكمية المتاحة)
        $remainingQuantity = $quantity;
        $assetsToSell = [];
        
        // ترتيب assets حسب الكمية المتاحة (تنازلي)
        $sortedAssets = $assets->sortByDesc('quantity');
        
        foreach ($sortedAssets as $asset) {
            if ($remainingQuantity <= 0) break;
            
            $assetUsedQty = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->sum('quantity');
            $assetAvailableQty = max(0, $asset->quantity - $assetUsedQty);
            
            if ($assetAvailableQty > 0) {
                $qtyFromThisAsset = min($remainingQuantity, $assetAvailableQty);
                $assetsToSell[] = [
                    'asset_id' => $asset->id,
                    'quantity' => $qtyFromThisAsset,
                    'batch_code' => $asset->batch ? $asset->batch->batch_code : 'N/A',
                ];
                $remainingQuantity -= $qtyFromThisAsset;
            }
        }

        $sellData = [
            'product_id'    => $productId,
            'sell_price'    => $sellPrice,
            'quantity'      => $quantity,
            'amount'        => $amount,
            'charge'        => $charge,
            'final_amount'  => $finalAmount,
            'available_quantity' => $availableQuantity,
            'assets_to_sell' => $assetsToSell, // array of {asset_id, quantity, batch_code}
        ];

        session()->put('sell_data', (object) $sellData);
        return to_route('user.sell.preview');
    }

    public function preview()
    {
        $pageTitle = 'Sell Preview';
        $sellData  = session('sell_data');
        if (!$sellData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.sell.form')->withNotify($notify);
        }
        
        // جلب جميع assets المراد بيعها
        $assetIds = array_column($sellData->assets_to_sell, 'asset_id');
        $assets = Asset::whereIn('id', $assetIds)->with('batch.product.unit', 'product.unit')->get();
        
        // جلب المنتج
        $product = \App\Models\Product::with('unit')->find($sellData->product_id);

        return view('Template::user.sell.preview', compact('pageTitle', 'sellData', 'assets', 'product'));
    }

    public function sellStore(Request $request)
    {
        $sellData = session('sell_data');
        if (!$sellData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.sell.form')->withNotify($notify);
        }

        $user = auth()->user();
        $productId = $sellData->product_id;
        $sellPrice = $sellData->sell_price;
        
        // جلب المنتج
        $product = \App\Models\Product::with('unit')->findOrFail($productId);

        // حساب متوسط سعر الشراء من كل المشتريات للمنتج (شامل الرسوم والضريبة)
        $buyHistories = \App\Models\BeanHistory::where('user_id', $user->id)
            ->where('type', Status::BUY_HISTORY)
            ->where('product_id', $productId)
            ->get();
        
        $totalCost = 0; // التكلفة الإجمالية (amount + charge + vat)
        $totalQty = 0;
        foreach ($buyHistories as $history) {
            if ($history->quantity > 0) {
                // التكلفة الفعلية = قيمة القهوة + الرسوم + الضريبة
                $cost = $history->amount + $history->charge + $history->vat;
                $totalCost += $cost;
                $totalQty += $history->quantity;
            }
        }
        
        // متوسط سعر الشراء الفعلي (شامل كل التكاليف)
        $averageBuyPrice = $totalQty > 0 ? ($totalCost / $totalQty) : 0;

        // إنشاء user_sell_order لكل asset
        foreach ($sellData->assets_to_sell as $assetData) {
            $asset = Asset::where('user_id', $user->id)->with('batch.product')->findOrFail($assetData['asset_id']);
            $assetQuantity = $assetData['quantity'];

            // التحقق من الكمية المتاحة مرة أخرى
            $totalQuantity = $asset->quantity;
            $usedQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->sum('quantity');
            $availableQuantity = max(0, $totalQuantity - $usedQuantity);

            if ($assetQuantity > $availableQuantity) {
                $notify[] = ['error', 'The available quantity for ' . ($asset->batch ? $asset->batch->batch_code : 'N/A') . ' is ' . showAmount($availableQuantity, 4, currencyFormat: false) . ' ' . ($product->unit->symbol ?? 'Unit')];
                return to_route('user.sell.form')->withNotify($notify);
            }

            // إنشاء user_sell_order
            $sellOrder = new \App\Models\UserSellOrder();
            $sellOrder->user_id = $user->id;
            $sellOrder->asset_id = $asset->id;
            $sellOrder->product_id = $asset->product_id;
            $sellOrder->warehouse_id = $asset->warehouse_id;
            $sellOrder->batch_id = $asset->batch_id;
            $sellOrder->buy_price = $averageBuyPrice; // متوسط سعر الشراء من كل المشتريات للمنتج
            $sellOrder->unit_id = $asset->unit_id;
            $sellOrder->item_unit_id = $product->unit_id ?? null;
            $sellOrder->currency_id = $product->currency_id ?? null;
            $sellOrder->quantity = $assetQuantity;
            $sellOrder->available_quantity = $assetQuantity; // نفس الكمية في البداية
            $sellOrder->sell_price = $sellPrice;
            $sellOrder->sell_order_code = \App\Models\UserSellOrder::generateSellOrderCode();
            $sellOrder->status = Status::SELL_ORDER_ACTIVE;
            $sellOrder->save();
            
            // تحديث سعر السوق للمنتج
            Batch::updateMarketPrice($productId);
        }

        // لا ننقص الكمية من asset مباشرة - سيتم إدارتها من خلال user_sell_orders
        // عندما يتم شراء الكمية من sell order، سيتم تقليل available_quantity

        $notify[] = ['success', 'Sell order(s) created successfully'];
        return to_route('user.sell.history')->withNotify($notify);
    }

    public function successPage()
    {
        $pageTitle   = 'Gold Sold';
        $sellHistory = session('sell_history');
        if (!$sellHistory) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.sell.history')->withNotify($notify);
        }
        
        // إعادة جلب sellHistory مع العلاقات
        $sellHistory = BeanHistory::with('batch.product.unit')->find($sellHistory->id);

        return view('Template::user.sell.success', compact('pageTitle', 'sellHistory'));
    }

    public function history()
    {
        $pageTitle     = 'Sell History';
        
        // جلب user_sell_orders بدلاً من bean_history
        $sellOrders = \App\Models\UserSellOrder::where('user_id', auth()->id())
            ->with(['product.unit', 'product.currency', 'batch', 'asset'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
        
        return view('Template::user.sell.history', compact('pageTitle', 'sellOrders'));
    }
}
