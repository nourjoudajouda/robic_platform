<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use GlobalStatus;
    
    protected $fillable = [
        'name',
        'location',
        'code',
        'address',
        'manager_name',
        'mobile',
        'max_capacity_unit',
        'max_capacity_kg',
        'area_sqm',
        'status'
    ];
}

