<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class PriceApi extends Model
{
    use GlobalStatus;

    protected $casts = [
        'configuration' => 'object',
    ];
}
