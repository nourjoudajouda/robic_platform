<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\BeanHistory;
use App\Models\RedeemData;
use App\Models\Transaction;

class BeanHistoryController extends Controller
{
    public function buy($userId = 0)
    {
        $pageTitle     = 'Buy History';
        $beanHistories = BeanHistory::buy();
        if ($userId) {
            $beanHistories->where('user_id', $userId);
        }
        $beanHistories = $beanHistories->with('batch.product', 'user', 'itemUnit', 'currency')->searchable(['user:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bean_history.list', compact('pageTitle', 'beanHistories'));
    }

    public function sell()
    {
        $pageTitle     = 'Sell History';
        $beanHistories = BeanHistory::sell()->with('batch.product', 'user', 'itemUnit', 'currency')->searchable(['user:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bean_history.list', compact('pageTitle', 'beanHistories'));
    }

    public function redeem()
    {
        $pageTitle     = 'Shipping & Receiving History';
        $beanHistories = BeanHistory::redeem()->with('product.unit', 'user', 'redeemData.shippingMethod', 'itemUnit', 'currency')->searchable(['user:username'])->filter(['redeemData:status'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bean_history.list', compact('pageTitle', 'beanHistories'));
    }

    public function gift()
    {
        $pageTitle     = 'Gift History';
        $beanHistories = BeanHistory::gift()->with('batch.product', 'user', 'recipient', 'itemUnit', 'currency')->searchable(['user:username', 'recipient:username'])->dateFilter()->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bean_history.list', compact('pageTitle', 'beanHistories'));
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

        $redeemHistory = $redeem->beanHistory;
        
        // إنشاء وصف للطلب من البيانات الجديدة
        $productName = $redeemHistory->product ? $redeemHistory->product->name : 'Green Coffee';
        $quantity = showAmount($redeemHistory->quantity, 4, currencyFormat: false);
        $unit = $redeemHistory->product && $redeemHistory->product->unit ? $redeemHistory->product->unit->symbol : 'kg';
        $sentence = $quantity . ' ' . $unit . ' of ' . $productName;

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
        
        // إرجاع تكلفة الشحن إلى رصيد المستخدم
        $user->balance += $beanHistory->charge;
        $user->save();

        // إرجاع الكمية إلى assets المستخدم
        // البحث عن أي asset موجود للمنتج والمستخدم، أو إنشاء واحد جديد
        $asset = \App\Models\Asset::where('user_id', $user->id)
            ->where('product_id', $beanHistory->product_id)
            ->first();
            
        if ($asset) {
            $asset->quantity += $beanHistory->quantity;
            $asset->save();
        } else {
            // إنشاء asset جديد إذا لم يكن موجوداً
            // (هذه حالة نادرة، لكن للأمان)
            \App\Models\Asset::create([
                'user_id' => $user->id,
                'product_id' => $beanHistory->product_id,
                'batch_id' => $beanHistory->batch_id,
                'warehouse_id' => $beanHistory->batch ? $beanHistory->batch->warehouse_id : null,
                'quantity' => $beanHistory->quantity,
            ]);
        }

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $beanHistory->charge;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Refund for cancelled shipping request';
        $transaction->trx          = getTrx();
        $transaction->remark       = 'redeem_cancelled';
        $transaction->save();

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
