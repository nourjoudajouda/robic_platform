<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'name_en', 'name_ar', 'code', 'symbol', 'description', 'description_en', 'description_ar'];
    
    // Accessors to get values based on current locale
    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['name_ar'] ?? null) {
            return $this->attributes['name_ar'];
        }
        return $this->attributes['name_en'] ?? $value;
    }
    
    public function getDescriptionAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->attributes['description_ar'] ?? null) {
            return $this->attributes['description_ar'];
        }
        return $this->attributes['description_en'] ?? $value;
    }
}

