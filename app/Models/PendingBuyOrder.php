<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class PendingBuyOrder extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'requested_quantity',
        'requested_price',
        'fulfilled_quantity',
        'pending_quantity',
        'notes',
        'order_code',
        'status',
        'expires_at',
        'notified_at',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:4',
        'requested_price' => 'decimal:2',
        'fulfilled_quantity' => 'decimal:4',
        'pending_quantity' => 'decimal:4',
        'expires_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * إنشاء كود طلب فريد
     */
    public static function generateOrderCode()
    {
        do {
            $code = 'PBO' . date('Ymd') . rand(1000, 9999);
        } while (self::where('order_code', $code)->exists());
        
        return $code;
    }

    /**
     * التحقق من انتهاء صلاحية الطلب
     */
    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * التحقق من إمكانية تنفيذ الطلب
     */
    public function canBeFulfilled($availableQuantity, $price)
    {
        return $this->status == Status::PENDING_BUY_ORDER 
            && !$this->isExpired()
            && $availableQuantity >= $this->pending_quantity 
            && abs($price - $this->requested_price) < 0.01; // نفس السعر تقريباً
    }
}
