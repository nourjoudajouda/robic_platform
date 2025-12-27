<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use GlobalStatus;
    
    protected $fillable = ['name', 'name_en', 'name_ar', 'karat', 'price', 'status'];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    
    // Accessor to get name based on current locale
    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->name_ar) {
            return $this->name_ar;
        }
        return $this->name_en ?? $value;
    }
}
