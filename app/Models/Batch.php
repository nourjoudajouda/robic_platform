<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\MarketPriceHistory;
use App\Models\BatchSellOrder;

class Batch extends Model
{
    use GlobalStatus;
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'units_count',
        'unit_id',
        'items_count_per_unit',
        'item_unit_id',
        'sell_price',
        'currency_id',
        'batch_code',
        'quality_grade',
        'origin_country',
        'exp_date',
        'buy_price',
        'status',
        'type',
        'user_id',
        'parent_ids'
    ];

    protected $casts = [
        'parent_ids' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellOrders()
    {
        return $this->hasMany(BatchSellOrder::class);
    }

    /**
     * حساب سعر السوق (المتوسط المرجح) لجميع batches لنفس المنتج
     * 
     * @param int $productId
     * @return float|null
     */
    public static function calculateMarketPrice($productId)
    {
        $totalValue = 0;
        $totalQuantity = 0;

        // جلب batch_sell_orders لنفس المنتج
        $batchSellOrders = BatchSellOrder::whereHas('batch', function($q) use ($productId) {
                $q->where('product_id', $productId)->where('status', Status::ENABLE);
            })
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->get();

        foreach ($batchSellOrders as $sellOrder) {
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            if ($availableQuantity > 0) {
                $orderValue = $availableQuantity * $sellOrder->sell_price;
                $totalValue += $orderValue;
                $totalQuantity += $availableQuantity;
            }
        }

        // جلب user_sell_orders لنفس المنتج
        $userSellOrders = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->get();

        foreach ($userSellOrders as $sellOrder) {
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            if ($availableQuantity > 0) {
                $orderValue = $availableQuantity * $sellOrder->sell_price;
                $totalValue += $orderValue;
                $totalQuantity += $availableQuantity;
            }
        }

        if ($totalQuantity > 0) {
            return $totalValue / $totalQuantity;
        }

        return null;
    }

    /**
     * حساب الكمية الإجمالية المتاحة لنفس المنتج
     * 
     * @param int $productId
     * @return float
     */
    public static function getTotalAvailableQuantity($productId)
    {
        // استخدام batch_sell_orders بدلاً من available_units_count
        $sellOrders = BatchSellOrder::whereHas('batch', function($q) use ($productId) {
                $q->where('product_id', $productId)->where('status', Status::ENABLE);
            })
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->get();

        $totalQuantity = 0;
        foreach ($sellOrders as $sellOrder) {
            $availableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
            $totalQuantity += $availableQuantity;
        }

        return $totalQuantity;
    }

    /**
     * تحديث سعر السوق وحفظه في جدول products وتسجيله في history
     * 
     * @param int $productId
     * @return void
     */
    public static function updateMarketPrice($productId)
    {
        $marketPrice = self::calculateMarketPrice($productId);
        
        $product = Product::find($productId);
        if ($product) {
            // تحديث سعر السوق في جدول products
            $oldMarketPrice = $product->market_price;
            $product->market_price = $marketPrice;
            $product->save();
            
            // تسجيل السعر الجديد في history فقط إذا تغير
            if ($marketPrice !== null && $oldMarketPrice != $marketPrice) {
                MarketPriceHistory::create([
                    'product_id' => $productId,
                    'market_price' => $marketPrice,
                ]);
            }
        }
    }

    /**
     * Get total available quantity from all sell orders for this batch
     * 
     * @return float
     */
    public function getAvailableQuantity()
    {
        $sellOrders = $this->sellOrders()
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->get();
        
        $totalQuantity = 0;
        foreach ($sellOrders as $sellOrder) {
            $totalQuantity += $sellOrder->available_quantity ?? $sellOrder->quantity;
        }
        
        return $totalQuantity;
    }

    /**
     * Get available quantity that can be used for new sell orders
     * (Total batch quantity - used in existing sell orders)
     * 
     * @return float
     */
    public function getAvailableQuantityForSellOrder()
    {
        // الكمية الإجمالية للباتش بالوحدات (unit_id من المنتج)
        $batchTotalQuantity = $this->units_count ?? 0;
        
        // حساب الكمية المستخدمة في كل batch_sell_orders (بغض النظر عن status)
        // نستخدم DB query مباشرة للحصول على أحدث البيانات من قاعدة البيانات
        // نستخدم fresh query بدون cache
        $usedQuantity = DB::table('batch_sell_orders')
            ->where('batch_id', $this->id)
            ->sum('quantity');
        
        // الكمية المتاحة للبيع = الكمية الإجمالية - الكمية المستخدمة
        $available = $batchTotalQuantity - ($usedQuantity ?? 0);
        
        return max(0, $available);
    }

    /**
     * Generate unique batch code automatically
     * Format: BATCH-XXX (for admin) or USER-XXX (for user sale)
     */
    public static function generateBatchCode($type = 'admin_created')
    {
        $prefix = $type === 'user_sale' ? 'USER' : 'BATCH';
        $code = '';
        
        do {
            $number = getNumber(5);
            $code = $prefix . '-' . $number;
        } while (self::where('batch_code', $code)->exists());
        
        return $code;
    }

}

