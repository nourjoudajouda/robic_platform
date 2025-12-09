<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use App\Constants\Status;
class RedeemUnit extends Model
{
    use GlobalStatus;

    public function scopeBar($query)
    {
        return $query->where('type', Status::REDEEM_UNIT_BAR);
    }

    public function scopeCoin($query)
    {
        return $query->where('type', Status::REDEEM_UNIT_COIN);
    }
}

