<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use GlobalStatus;

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
