<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'cost_per_kg',
        'status',
    ];

    protected $casts = [
        'cost_per_kg' => 'decimal:8',
        'status' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
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

