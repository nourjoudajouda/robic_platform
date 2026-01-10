<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class UserSellOrder extends Model
{
    use GlobalStatus;
    
    protected $fillable = [
        'user_id',
        'asset_id',
        'product_id',
        'warehouse_id',
        'batch_id',
        'buy_price',
        'unit_id',
        'item_unit_id',
        'currency_id',
        'quantity',
        'available_quantity',
        'sell_price',
        'sell_order_code',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
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

    /**
     * Generate unique sell order code automatically
     * Format: SO-XXX (SO for Sell Order + random number)
     */
    public static function generateSellOrderCode()
    {
        $prefix = 'SO';
        $code = '';
        
        do {
            $number = getNumber(3);
            $code = $prefix . '-' . $number;
        } while (self::where('sell_order_code', $code)->exists());
        
        return $code;
    }

    /**
     * Get available quantity for sale
     */
    public function getAvailableQuantity()
    {
        return $this->available_quantity ?? $this->quantity;
    }

    /**
     * Decrease available quantity after sale
     */
    public function decreaseAvailableQuantity($quantity)
    {
        $availableQuantity = $this->available_quantity ?? $this->quantity;
        
        if ($quantity >= $availableQuantity) {
            $this->available_quantity = 0;
            $this->status = Status::SELL_ORDER_SOLD; // Mark as sold
        } else {
            $this->available_quantity = $availableQuantity - $quantity;
        }
        
        $this->save();
        
        // التحقق من الطلبات المعلقة بعد تحديث الكمية
        $this->checkPendingBuyOrders();
    }
    
    /**
     * التحقق من الطلبات المعلقة عند توفر الكمية
     */
    public function checkPendingBuyOrders()
    {
        if (!$this->product_id) {
            return;
        }
        
        // جلب الطلبات المعلقة لهذا المنتج والسعر
        $pendingOrders = \App\Models\PendingBuyOrder::where('product_id', $this->product_id)
            ->where('requested_price', $this->sell_price)
            ->where('status', Status::PENDING_BUY_ORDER)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->with('user', 'product')
            ->get();
        
        foreach ($pendingOrders as $pendingOrder) {
            // حساب الكمية المتوفرة بالسعر المطلوب
            $availableQuantity = $this->getAvailableQuantityAtPrice($this->product_id, $this->sell_price);
            
            if ($availableQuantity >= $pendingOrder->pending_quantity) {
                // التحقق من عدم إرسال إشعار مؤخراً (خلال آخر ساعة)
                if (!$pendingOrder->notified_at || $pendingOrder->notified_at->lt(now()->subHour())) {
                    notify($pendingOrder->user, 'PENDING_ORDER_AVAILABLE', [
                        'product' => $pendingOrder->product->name ?? 'Green Coffee',
                        'quantity' => showAmount($pendingOrder->pending_quantity, 4, currencyFormat: false),
                        'price' => showAmount($pendingOrder->requested_price, 2),
                        'available_quantity' => showAmount($availableQuantity, 4, currencyFormat: false),
                        'order_code' => $pendingOrder->order_code,
                    ]);
                    
                    $pendingOrder->notified_at = now();
                    $pendingOrder->save();
                }
            }
        }
    }
    
    /**
     * Override badgeData to show custom status badges
     */
    public function badgeData()
    {
        $html = '';
        if ($this->status == Status::SELL_ORDER_ACTIVE) {
            $html = '<span class="badge badge--success">' . trans('Active') . '</span>';
        } elseif ($this->status == Status::SELL_ORDER_INACTIVE) {
            $html = '<span class="badge badge--warning">' . trans('Inactive') . '</span>';
        } elseif ($this->status == Status::SELL_ORDER_SOLD) {
            $html = '<span class="badge badge--info">' . trans('Sold') . '</span>';
        } elseif ($this->status == Status::SELL_ORDER_CANCELLED) {
            $html = '<span class="badge badge--danger">' . trans('Cancelled') . '</span>';
        } else {
            $html = '<span class="badge badge--warning">' . trans('Unknown') . '</span>';
        }
        return $html;
    }

    /**
     * الحصول على الكمية المتوفرة بسعر معين
     */
    private function getAvailableQuantityAtPrice($productId, $price)
    {
        $batchOrders = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where('sell_price', $price)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        $userOrders = self::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where('sell_price', $price)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        return $batchOrders + $userOrders;
    }
}
