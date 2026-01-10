<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPriceHistory extends Model
{
    use HasFactory;
    protected $table = 'market_price_history';
    
    protected $fillable = [
        'product_id',
        'market_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * جلب تاريخ الأسعار لمنتج معين
     * 
     * @param int $productId
     * @param int|null $limit عدد السجلات المطلوبة
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPriceHistory($productId, $limit = null)
    {
        $query = self::where('product_id', $productId)
            ->orderBy('created_at', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * جلب تاريخ الأسعار لجميع المنتجات
     * 
     * @param int|null $limit عدد السجلات المطلوبة
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllPriceHistory($limit = null)
    {
        $query = self::with('product')
            ->orderBy('created_at', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
}

