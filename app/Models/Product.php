<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, GlobalStatus;
    
    protected $fillable = ['name', 'name_en', 'name_ar', 'sku', 'status', 'market_price', 'unit_id', 'currency_id'];

    public function marketPriceHistory()
    {
        return $this->hasMany(MarketPriceHistory::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    
    // Accessor to get name based on current locale
    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['name_ar'] ?? null) {
            return $this->attributes['name_ar'];
        }
        return $this->attributes['name_en'] ?? $value;
    }
}
