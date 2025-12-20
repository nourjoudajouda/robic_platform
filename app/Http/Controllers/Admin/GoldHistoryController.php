<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\BeanHistory;
use App\Models\RedeemData;
use App\Models\Transaction;

class GoldHistoryController extends Controller
{
    public function buy($userId = 0)
    {
        $pageTitle     = 'Buy History';
        $goldHistories = BeanHistory::buy();
        if ($userId) {
            $goldHistories->where('user_id', $userId);
        }
        $goldHistories = $goldHistories->with('batch.product', 'user', 'itemUnit', 'currency')->searchable(['user:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.gold_history.list', compact('pageTitle', 'goldHistories'));
    }

    public function sell()
    {
        $pageTitle     = 'Sell History';
        $goldHistories = BeanHistory::sell()->with('batch.product', 'user', 'itemUnit', 'currency')->searchable(['user:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.gold_history.list', compact('pageTitle', 'goldHistories'));
    }

    public function redeem()
    {
        $pageTitle     = 'Redeem History';
        $goldHistories = BeanHistory::redeem()->with('batch.product', 'user', 'redeemData', 'itemUnit', 'currency')->searchable(['user:username'])->filter(['redeemData:status'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.gold_history.list', compact('pageTitle', 'goldHistories'));
    }

    public function gift()
    {
        $pageTitle     = 'Gift History';
        $goldHistories = BeanHistory::gift()->with('batch.product', 'user', 'recipient', 'itemUnit', 'currency')->searchable(['user:username', 'recipient:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.gold_history.list', compact('pageTitle', 'goldHistories'));
    }

    public function redeemStatus($id, $status)
    {
        $redeem = RedeemData::findOrFail($id);

        if ($redeem->status == Status::REDEEM_STATUS_CANCELLED || $redeem->status == Status::REDEEM_STATUS_DELIVERED) {
            $notify[] = ['error', 'This redeem request has already been ' . ($redeem->status == Status::REDEEM_STATUS_CANCELLED ? 'cancelled' : 'delivered') . ' and cannot be modified'];
            return back()->withNotify($notify);
        }

        if ($redeem->status == $status) {
            $statusWords = ['processing', 'shipped', 'delivered', 'cancelled'];
            $statusText  = $statusWords[$status - 1];

            $notify[] = ['error', 'Redeem status is already changed to ' . $statusText];
            return back()->withNotify($notify);
        }

        $sentence      = collect($redeem->order_details->items)->pluck('text')->toArray();
        $sentence      = count($sentence) > 1 ? implode(', ', array_slice($sentence, 0, -1)) . ' and ' . end($sentence) : $sentence[0];
        $redeemHistory = $redeem->beanHistory;

        if ($status == Status::REDEEM_STATUS_SHIPPED || $status == Status::REDEEM_STATUS_DELIVERED) {
            if ($status == Status::REDEEM_STATUS_SHIPPED) {
                $redeem->status = Status::REDEEM_STATUS_SHIPPED;
                $mailTemplate   = 'REDEEM_SHIPPED';
                $statusText     = 'shipped';
            } else {
                $redeem->status = Status::REDEEM_STATUS_DELIVERED;
                $mailTemplate   = 'REDEEM_DELIVERED';
                $statusText     = 'delivered';
            }
            $redeem->save();

            $productName = $redeemHistory->batch && $redeemHistory->batch->product ? $redeemHistory->batch->product->name : 'Green Coffee';
            notify($redeemHistory->user, $mailTemplate, [
                'category' => $productName,
                'quantity' => showAmount($redeemHistory->quantity, currencyFormat: false),
                'amount'   => showAmount($redeemHistory->amount),
                'charge'   => showAmount($redeemHistory->charge),
                'trx'      => $redeemHistory->trx,
                'details'  => $sentence,
            ]);

            $notify[] = ['success', "Redeem status changed to $statusText successfully"];
            return back()->withNotify($notify);
        }

        $redeem->status = Status::REDEEM_STATUS_CANCELLED;
        $redeem->save();

        $beanHistory = $redeem->beanHistory;
        $user        = $beanHistory->user;
        $user->balance += $beanHistory->charge;
        $user->save();

        $asset = $beanHistory->asset;
        if ($asset) {
            $asset->quantity += $beanHistory->quantity;
            $asset->save();
        }

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $beanHistory->charge;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Refund for cancelled redeem request';
        $transaction->trx          = getTrx();
        $transaction->remark       = 'redeem_cancelled';
        $transaction->save();

        $productName = $beanHistory->batch && $beanHistory->batch->product ? $beanHistory->batch->product->name : 'Green Coffee';
        notify($user, 'REDEEM_CANCELLED', [
            'category' => $productName,
            'quantity' => showAmount($beanHistory->quantity, currencyFormat: false),
            'amount'   => showAmount($beanHistory->amount),
            'charge'   => showAmount($beanHistory->charge),
            'trx'      => $beanHistory->trx,
            'details'  => $sentence,
        ]);

        $notify[] = ['success', 'Redeem status changed to cancelled successfully'];
        return back()->withNotify($notify);
    }
}
