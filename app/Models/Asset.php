<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public static function buyGold($user, $category, $amount, $totalAmount, $quantity, $charge, $vat, $methodName = null)
    {
        $user->balance -= $totalAmount;
        $user->save();

        $asset = self::where('user_id', $user->id)->where('category_id', $category->id)->first();

        if (!$asset) {
            $asset              = new self();
            $asset->user_id     = $user->id;
            $asset->category_id = $category->id;
        }

        $asset->quantity += $quantity;
        $asset->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $totalAmount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = $charge;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Buy ' . $category->name . ' via ' . ($methodName ?? 'main balance');
        $transaction->trx          = getTrx();
        $transaction->remark       = 'buy_gold';
        $transaction->save();

        $buyHistory              = new GoldHistory();
        $buyHistory->user_id     = $user->id;
        $buyHistory->asset_id    = $asset->id;
        $buyHistory->category_id = $category->id;
        $buyHistory->quantity    = $quantity;
        $buyHistory->amount      = $amount;
        $buyHistory->charge      = $charge;
        $buyHistory->vat         = $vat;
        $buyHistory->trx         = $transaction->trx;
        $buyHistory->type        = Status::BUY_HISTORY;
        $buyHistory->save();

        notify($user, 'BUY_GOLD', [
            'category' => $category->name,
            'quantity' => showAmount($quantity, 4, currencyFormat: false),
            'amount'   => showAmount($amount),
            'charge'   => showAmount($charge),
            'vat'      => showAmount($vat),
            'trx'      => $transaction->trx,
        ]);

        return $buyHistory;
    }
}
