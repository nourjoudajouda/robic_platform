<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use GlobalStatus;
    
    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'location',
        'location_en',
        'location_ar',
        'code',
        'address',
        'address_en',
        'address_ar',
        'latitude',
        'longitude',
        'manager_name',
        'manager_name_en',
        'manager_name_ar',
        'mobile',
        'max_capacity_unit',
        'max_capacity_kg',
        'area_sqm',
        'status'
    ];
    
    // Accessors to get values based on current locale
    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['name_ar'] ?? null) {
            return $this->attributes['name_ar'];
        }
        return $this->attributes['name_en'] ?? $value;
    }
    
    public function getLocationAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['location_ar'] ?? null) {
            return $this->attributes['location_ar'];
        }
        return $this->attributes['location_en'] ?? $value;
    }
    
    public function getAddressAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['address_ar'] ?? null) {
            return $this->attributes['address_ar'];
        }
        return $this->attributes['address_en'] ?? $value;
    }
    
    public function getManagerNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['manager_name_ar'] ?? null) {
            return $this->attributes['manager_name_ar'];
        }
        return $this->attributes['manager_name_en'] ?? $value;
    }
}

