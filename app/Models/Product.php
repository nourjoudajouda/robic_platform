<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use GlobalStatus;
    
    protected $fillable = ['name', 'sku', 'status', 'market_price', 'unit_id', 'currency_id'];

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
}
