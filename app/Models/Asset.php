<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Asset extends Model
{
    protected $fillable = [
        'user_id',
        'batch_id',
        'product_id',
        'warehouse_id',
        'buy_price',
        'unit_id',
        'item_unit_id',
        'currency_id',
        'quantity'
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function itemUnit()
    {
        return $this->belongsTo(Unit::class, 'item_unit_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public static function buyBean($user, $batch, $sellOrder, $amount, $totalAmount, $quantity, $charge, $vat, $methodName = null)
    {
        return DB::transaction(function () use ($user, $batch, $sellOrder, $amount, $totalAmount, $quantity, $charge, $vat, $methodName) {
            // تحديث رصيد المستخدم
            $user->balance -= $totalAmount;
            $user->save();

            // تحديث wallet
            $wallet = Wallet::where('user_id', $user->id)->first();
            if ($wallet) {
                $wallet->balance -= $totalAmount;
                $wallet->save();
            }

            // تقليل الكمية المتاحة من batch_sell_order
            $sellOrder->decreaseAvailableQuantity($quantity);

            // البحث عن asset أو إنشاء جديد
            // نبحث عن asset بنفس batch_id أو ننشئ واحد جديد
            $asset = self::where('user_id', $user->id)->where('batch_id', $batch->id)->lockForUpdate()->first();

            if (!$asset) {
                $asset              = new self();
                $asset->user_id     = $user->id;
                $asset->batch_id    = $batch->id;
                // حفظ كل التفاصيل من batch
                $asset->product_id  = $batch->product_id;
                $asset->warehouse_id = $batch->warehouse_id;
                $asset->buy_price   = $sellOrder->sell_price; // سعر الشراء = سعر البيع من sell order
                $asset->unit_id     = $batch->unit_id;
                $asset->item_unit_id = $batch->product->unit_id ?? null;
                $asset->currency_id = $batch->product->currency_id ?? null;
            }

            $asset->quantity += $quantity;
            $asset->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $totalAmount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $charge;
            $transaction->trx_type     = '-';
            $transaction->details      = 'Buy Green Coffee via ' . ($methodName ?? 'main balance');
            $transaction->trx          = getTrx();
            $transaction->remark       = 'buy_bean';
            $transaction->save();

            $buyHistory              = new BeanHistory();
            $buyHistory->user_id     = $user->id;
            $buyHistory->asset_id    = $asset->id;
            $buyHistory->batch_id    = $batch->id;
            $buyHistory->quantity    = $quantity;
            $buyHistory->item_unit_id = $batch->product->unit_id ?? null;
            $buyHistory->amount      = $amount;
            $buyHistory->currency_id = $batch->product->currency_id ?? null;
            
            // تحديث سعر السوق للمنتج بعد الشراء
            Batch::updateMarketPrice($batch->product_id);
            $buyHistory->charge      = $charge;
            $buyHistory->vat         = $vat;
            $buyHistory->trx         = $transaction->trx;
            $buyHistory->type        = Status::BUY_HISTORY;
            $buyHistory->save();

            notify($user, 'BUY_BEAN', [
                'product' => $batch->product->name ?? 'Green Coffee',
                'quantity' => showAmount($quantity, 4, currencyFormat: false),
                'amount'   => showAmount($amount),
                'charge'   => showAmount($charge),
                'vat'      => showAmount($vat),
                'trx'      => $transaction->trx,
            ]);

            return $buyHistory;
        });
    }

    /**
     * شراء من user_sell_order
     */
    public static function buyFromUserSellOrder($user, $userSellOrder, $amount, $totalAmount, $quantity, $charge, $vat, $methodName = null)
    {
        return DB::transaction(function () use ($user, $userSellOrder, $amount, $totalAmount, $quantity, $charge, $vat, $methodName) {
            // تحديث رصيد المستخدم
            $user->balance -= $totalAmount;
            $user->save();

            // تحديث wallet
            $wallet = Wallet::where('user_id', $user->id)->first();
            if ($wallet) {
                $wallet->balance -= $totalAmount;
                $wallet->save();
            }

            // تقليل الكمية المتاحة من user_sell_order
            $userSellOrder->decreaseAvailableQuantity($quantity);

            // تحديث رصيد البائع (صاحب user_sell_order)
            $seller = $userSellOrder->user;
            $sellerAmount = $amount; // المبلغ الذي سيحصل عليه البائع (بدون charge)
            $seller->balance += $sellerAmount;
            $seller->save();

            // إنشاء transaction للبائع
            $sellerTransaction = new Transaction();
            $sellerTransaction->user_id = $seller->id;
            $sellerTransaction->amount = $sellerAmount;
            $sellerTransaction->post_balance = $seller->balance;
            $sellerTransaction->charge = 0;
            $sellerTransaction->trx_type = '+';
            $sellerTransaction->details = 'Sell Green Coffee to user #' . $user->id;
            $sellerTransaction->trx = getTrx();
            $sellerTransaction->remark = 'sell_bean';
            $sellerTransaction->save();

            // البحث عن asset أو إنشاء جديد
            // نبحث عن asset بنفس batch_id أو ننشئ واحد جديد
            $batch = $userSellOrder->batch;
            $asset = null;
            
            if ($batch) {
                $asset = self::where('user_id', $user->id)->where('batch_id', $batch->id)->lockForUpdate()->first();
            } else {
                // إذا لم يكن هناك batch، نبحث عن asset بنفس product_id
                $asset = self::where('user_id', $user->id)
                    ->where('product_id', $userSellOrder->product_id)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$asset) {
                $asset = new self();
                $asset->user_id = $user->id;
                $asset->batch_id = $batch ? $batch->id : null;
                $asset->product_id = $userSellOrder->product_id;
                $asset->warehouse_id = $userSellOrder->warehouse_id;
                $asset->buy_price = $userSellOrder->sell_price; // سعر الشراء = سعر البيع من sell order
                $asset->unit_id = $userSellOrder->unit_id;
                $asset->item_unit_id = $userSellOrder->item_unit_id;
                $asset->currency_id = $userSellOrder->currency_id;
            }

            $asset->quantity += $quantity;
            $asset->save();

            // Transaction للمشتري
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalAmount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = $charge;
            $transaction->trx_type = '-';
            $transaction->details = 'Buy Green Coffee from user #' . $seller->id . ' via ' . ($methodName ?? 'main balance');
            $transaction->trx = getTrx();
            $transaction->remark = 'buy_bean';
            $transaction->save();

            // BeanHistory للمشتري
            $buyHistory = new BeanHistory();
            $buyHistory->user_id = $user->id;
            $buyHistory->asset_id = $asset->id;
            $buyHistory->batch_id = $batch ? $batch->id : null;
            $buyHistory->product_id = $userSellOrder->product_id;
            $buyHistory->quantity = $quantity;
            $buyHistory->item_unit_id = $userSellOrder->item_unit_id;
            $buyHistory->amount = $amount;
            $buyHistory->currency_id = $userSellOrder->currency_id;
            $buyHistory->charge = $charge;
            $buyHistory->vat = $vat;
            $buyHistory->trx = $transaction->trx;
            $buyHistory->type = Status::BUY_HISTORY;
            $buyHistory->save();

            // BeanHistory للبائع
            $sellHistory = new BeanHistory();
            $sellHistory->user_id = $seller->id;
            $sellHistory->asset_id = $userSellOrder->asset_id;
            $sellHistory->batch_id = $batch ? $batch->id : null;
            $sellHistory->product_id = $userSellOrder->product_id;
            $sellHistory->quantity = $quantity;
            $sellHistory->item_unit_id = $userSellOrder->item_unit_id;
            $sellHistory->amount = $sellerAmount;
            $sellHistory->currency_id = $userSellOrder->currency_id;
            $sellHistory->charge = 0;
            $sellHistory->trx = $sellerTransaction->trx;
            $sellHistory->type = Status::SELL_HISTORY;
            $sellHistory->save();

            // تحديث سعر السوق للمنتج بعد الشراء
            if ($userSellOrder->product_id) {
                Batch::updateMarketPrice($userSellOrder->product_id);
            }

            notify($user, 'BUY_BEAN', [
                'product' => $userSellOrder->product->name ?? 'Green Coffee',
                'quantity' => showAmount($quantity, 4, currencyFormat: false),
                'amount' => showAmount($amount),
                'charge' => showAmount($charge),
                'vat' => showAmount($vat),
                'trx' => $transaction->trx,
            ]);

            notify($seller, 'SELL_BEAN', [
                'product' => $userSellOrder->product->name ?? 'Green Coffee',
                'quantity' => showAmount($quantity, 4, currencyFormat: false),
                'amount' => showAmount($sellerAmount),
                'trx' => $sellerTransaction->trx,
            ]);

            return $buyHistory;
        });
    }

    /**
     * شراء من عدة sell orders بدءاً من أرخص سعر
     */
    public static function buyFromMultipleOrders($user, $productId, $multipleOrders, $totalAmount, $totalQuantity, $charge, $vat, $methodName = null)
    {
        return DB::transaction(function () use ($user, $productId, $multipleOrders, $totalAmount, $totalQuantity, $charge, $vat, $methodName) {
            // حساب المبلغ الإجمالي (مع الرسوم والضريبة)
            $finalAmount = $totalAmount + $charge + $vat;
            
            // تحديث رصيد المستخدم
            $user->balance -= $finalAmount;
            $user->save();

            // تحديث wallet
            $wallet = Wallet::where('user_id', $user->id)->first();
            if ($wallet) {
                $wallet->balance -= $finalAmount;
                $wallet->save();
            }

            // جلب المنتج
            $product = Product::findOrFail($productId);
            
            // معالجة كل order
            $buyHistories = [];
            $trx = getTrx();
            $orderIndex = 0;
            $totalOrdersCount = count($multipleOrders);
            
            foreach ($multipleOrders as $orderData) {
                $orderType = $orderData['type'];
                $orderId = $orderData['order_id'];
                $qty = $orderData['quantity'];
                $price = $orderData['price'];
                $orderAmount = $qty * $price;
                $orderIndex++;
                
                // حساب الضرائب والرسوم مرة واحدة فقط في أول order
                // باقي الأوامر charge و vat = 0
                if ($orderIndex === 1) {
                    // أول order: نضع كل الـ charge و vat عليه
                    $orderCharge = $charge;
                    $orderVat = $vat;
                } else {
                    // باقي الأوامر: بدون charge أو vat
                    $orderCharge = 0;
                    $orderVat = 0;
                }
                
                if ($orderType == 'batch') {
                    $sellOrder = \App\Models\BatchSellOrder::findOrFail($orderId);
                    $batch = $sellOrder->batch;
                    
                    // تقليل الكمية المتاحة
                    $sellOrder->decreaseAvailableQuantity($qty);
                    
                    // البحث عن asset أو إنشاء جديد
                    $asset = self::where('user_id', $user->id)->where('batch_id', $batch->id)->lockForUpdate()->first();
                    
                    if (!$asset) {
                        $asset = new self();
                        $asset->user_id = $user->id;
                        $asset->batch_id = $batch->id;
                        $asset->product_id = $batch->product_id;
                        $asset->warehouse_id = $batch->warehouse_id;
                        $asset->buy_price = $price; // سعر الشراء من هذا order
                        $asset->unit_id = $batch->unit_id;
                        $asset->item_unit_id = $batch->product->unit_id ?? null;
                        $asset->currency_id = $batch->product->currency_id ?? null;
                        $asset->quantity = 0;
                    }
                    
                    $asset->quantity += $qty;
                    $asset->save();
                    
                    // BeanHistory - مع تخزين charge و vat الموزعة
                    $buyHistory = new BeanHistory();
                    $buyHistory->user_id = $user->id;
                    $buyHistory->asset_id = $asset->id;
                    $buyHistory->batch_id = $batch->id;
                    $buyHistory->product_id = $batch->product_id; // إضافة product_id
                    $buyHistory->quantity = $qty;
                    $buyHistory->item_unit_id = $batch->product->unit_id ?? null;
                    $buyHistory->amount = $orderAmount;
                    $buyHistory->currency_id = $batch->product->currency_id ?? null;
                    $buyHistory->charge = $orderCharge; // حفظ الـ charge الموزع
                    $buyHistory->vat = $orderVat; // حفظ الـ VAT الموزع
                    $buyHistory->trx = $trx;
                    $buyHistory->type = Status::BUY_HISTORY;
                    $buyHistory->save();
                    
                    $buyHistories[] = [
                        'history' => $buyHistory,
                        'charge' => $orderCharge,
                        'vat' => $orderVat,
                    ];
                    
                } else { // user
                    $userSellOrder = \App\Models\UserSellOrder::findOrFail($orderId);
                    $batch = $userSellOrder->batch;
                    
                    // تقليل الكمية المتاحة
                    $userSellOrder->decreaseAvailableQuantity($qty);
                    
                    // تحديث رصيد البائع
                    $seller = $userSellOrder->user;
                    $seller->balance += $orderAmount;
                    $seller->save();
                    
                    // Transaction للبائع
                    $sellerTransaction = new Transaction();
                    $sellerTransaction->user_id = $seller->id;
                    $sellerTransaction->amount = $orderAmount;
                    $sellerTransaction->post_balance = $seller->balance;
                    $sellerTransaction->charge = 0;
                    $sellerTransaction->trx_type = '+';
                    $sellerTransaction->details = 'Sell Green Coffee to user #' . $user->id;
                    $sellerTransaction->trx = getTrx();
                    $sellerTransaction->remark = 'sell_bean';
                    $sellerTransaction->save();
                    
                    // البحث عن asset أو إنشاء جديد
                    $asset = null;
                    if ($batch) {
                        $asset = self::where('user_id', $user->id)->where('batch_id', $batch->id)->lockForUpdate()->first();
                    } else {
                        $asset = self::where('user_id', $user->id)
                            ->where('product_id', $productId)
                            ->lockForUpdate()
                            ->first();
                    }
                    
                    if (!$asset) {
                        $asset = new self();
                        $asset->user_id = $user->id;
                        $asset->batch_id = $batch ? $batch->id : null;
                        $asset->product_id = $productId;
                        $asset->warehouse_id = $userSellOrder->warehouse_id;
                        $asset->buy_price = $price;
                        $asset->unit_id = $userSellOrder->unit_id;
                        $asset->item_unit_id = $userSellOrder->item_unit_id;
                        $asset->currency_id = $userSellOrder->currency_id;
                        $asset->quantity = 0;
                    }
                    
                    $asset->quantity += $qty;
                    $asset->save();
                    
                    // BeanHistory للمشتري - مع تخزين charge و vat الموزعة
                    $buyHistory = new BeanHistory();
                    $buyHistory->user_id = $user->id;
                    $buyHistory->asset_id = $asset->id;
                    $buyHistory->batch_id = $batch ? $batch->id : null;
                    $buyHistory->product_id = $productId;
                    $buyHistory->quantity = $qty;
                    $buyHistory->item_unit_id = $userSellOrder->item_unit_id;
                    $buyHistory->amount = $orderAmount;
                    $buyHistory->currency_id = $userSellOrder->currency_id;
                    $buyHistory->charge = $orderCharge; // حفظ الـ charge الموزع
                    $buyHistory->vat = $orderVat; // حفظ الـ VAT الموزع
                    $buyHistory->trx = $trx;
                    $buyHistory->type = Status::BUY_HISTORY;
                    $buyHistory->save();
                    
                    // BeanHistory للبائع (بدون charge/vat لأنه بائع)
                    $sellHistory = new BeanHistory();
                    $sellHistory->user_id = $seller->id;
                    $sellHistory->asset_id = $userSellOrder->asset_id;
                    $sellHistory->batch_id = $batch ? $batch->id : null;
                    $sellHistory->product_id = $productId;
                    $sellHistory->quantity = $qty;
                    $sellHistory->item_unit_id = $userSellOrder->item_unit_id;
                    $sellHistory->amount = $orderAmount;
                    $sellHistory->currency_id = $userSellOrder->currency_id;
                    $sellHistory->charge = 0;
                    $sellHistory->trx = $sellerTransaction->trx;
                    $sellHistory->type = Status::SELL_HISTORY;
                    $sellHistory->save();
                    
                    $buyHistories[] = [
                        'history' => $buyHistory,
                        'charge' => $orderCharge,
                        'vat' => $orderVat,
                    ];
                }
            }
            
            // Transaction واحد للمشتري (للمبلغ الإجمالي)
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $finalAmount; // المبلغ الكلي المخصوم من الرصيد
            $transaction->post_balance = $user->balance;
            $transaction->charge = $charge;
            $transaction->trx_type = '-';
            $transaction->details = 'Buy Green Coffee from multiple orders via ' . ($methodName ?? 'main balance');
            $transaction->trx = $trx;
            $transaction->remark = 'buy_bean';
            $transaction->save();
            
            // تحديث سعر السوق للمنتج بعد الشراء
            Batch::updateMarketPrice($productId);
            
            notify($user, 'BUY_BEAN', [
                'product' => $product->name ?? 'Green Coffee',
                'quantity' => showAmount($totalQuantity, 4, currencyFormat: false),
                'amount' => showAmount($totalAmount), // قيمة القهوة فقط
                'charge' => showAmount($charge),
                'vat' => showAmount($vat),
                'trx' => $trx,
            ]);
            
            // إرجاع أول buy history object
            return $buyHistories[0]['history'] ?? null;
        });
    }
}
