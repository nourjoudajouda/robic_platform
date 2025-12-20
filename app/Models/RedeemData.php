<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Constants\Status;
use App\Traits\GlobalStatus;

class RedeemData extends Model
{
    use GlobalStatus;

    protected $casts = [
        'order_details'    => 'object'
    ];

    public function goldHistory()
    {
        return $this->belongsTo(GoldHistory::class);
    }
    
    public function beanHistory()
    {
        // استخدام bean_history_id إذا كان موجوداً، وإلا استخدم gold_history_id
        $foreignKey = \Schema::hasColumn($this->getTable(), 'bean_history_id') ? 'bean_history_id' : 'gold_history_id';
        return $this->belongsTo(BeanHistory::class, $foreignKey);
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
        if ($this->status == Status::REDEEM_STATUS_PROCESSING) {
            $html = '<span class="badge badge--warning">' . trans('Processing') . '</span>';
        } elseif ($this->status == Status::REDEEM_STATUS_SHIPPED) {
            $html = '<span class="badge badge--info">' . trans('Shipped') . '</span>';
        } elseif ($this->status == Status::REDEEM_STATUS_DELIVERED) {
            $html = '<span class="badge badge--success">' . trans('Delivered') . '</span>';
        } else {
            $html = '<span class="badge badge--danger">' . trans('Cancelled') . '</span>';
        }
        return $html;
    }
}
