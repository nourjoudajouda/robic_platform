<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\ApiQuery;

class GoldHistory extends Model
{
    use ApiQuery;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function redeemData()
    {
        return $this->hasOne(RedeemData::class);
    }

    public function scopeBuy($query)
    {
        return $query->where('type', Status::BUY_HISTORY);
    }

    public function scopeSell($query)
    {
        return $query->where('type', Status::SELL_HISTORY);
    }

    public function scopeRedeem($query)
    {
        return $query->where('type', Status::REDEEM_HISTORY);
    }

    public function scopeGift($query)
    {
        return $query->where('type', Status::GIFT_HISTORY);
    }

    public function scopeRedeemOrGift($query)
    {
        return $query->whereIn('type', [Status::REDEEM_HISTORY, Status::GIFT_HISTORY]);
    }

    public function scopeBuyOrSell($query)
    {
        return $query->whereIn('type', [Status::BUY_HISTORY, Status::SELL_HISTORY]);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        $html = '';
        if ($this->type == Status::BUY_HISTORY) {
            $html = '<span class="badge badge--success">' . trans('Buy') . '</span>';
        } else if ($this->type == Status::SELL_HISTORY) {
            $html = '<span class="badge badge--danger">' . trans('Sell') . '</span>';
        } else if ($this->type == Status::REDEEM_HISTORY) {
            $html = '<span class="badge badge--orange">' . trans('Redeem') . '</span>';
        } else if ($this->type == Status::GIFT_HISTORY) {
            $html = '<span class="badge badge--warning">' . trans('Gift') . '</span>';
        }
        return $html;
    }


}

