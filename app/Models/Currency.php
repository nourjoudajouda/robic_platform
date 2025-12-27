<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['name', 'name_en', 'name_ar', 'code', 'symbol'];
    
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

