<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\ChargeLimit;
use App\Models\BeanHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiftController extends Controller
{
    public function giftForm()
    {
        $pageTitle   = 'Gift Form';
        $assets      = Asset::where('user_id', '=', auth()->id())->with('batch.product.unit')->get();
        $chargeLimit = ChargeLimit::where('slug', 'gift')->first();
        return view('Template::user.gift.form', compact('pageTitle', 'assets', 'chargeLimit'));
    }

    public function giftStore(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'amount'   => 'required|numeric',
            'user'     => 'required',
        ]);

        $user          = auth()->user();
        $recipientUser = User::where('username', $request->user)->orWhere('email', $request->user)->first();

        if (!$recipientUser) {
            $notify[] = ['error', 'Invalid recipient'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        if ($recipientUser->id == $user->id) {
            $notify[] = ['error', 'You cannot gift to yourself'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $chargeLimit = ChargeLimit::where('slug', 'gift')->first();

        if ($chargeLimit->min_amount > $request->amount) {
            $notify[] = ['error', 'The minimum gift amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }
        if ($chargeLimit->max_amount < $request->amount) {
            $notify[] = ['error', 'The maximum gift amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $asset    = Asset::where('user_id', $user->id)->with('batch')->findOrFail($request->asset_id);
        if (!$asset->batch) {
            $notify[] = ['error', 'Invalid asset'];
            return back()->withNotify($notify);
        }
        $quantity = $request->amount / $asset->batch->sell_price;

        if ($quantity > $asset->quantity) {
            $notify[] = ['error', 'Insufficient bean asset'];
            return back()->withNotify($notify);
        }

        $charge = $chargeLimit->fixed_charge + $request->amount * $chargeLimit->percent_charge / 100;

        if ($charge > $user->balance) {
            $notify[] = ['error', 'Insufficient balance for charge'];
            return back()->withNotify($notify);
        }

        $asset->quantity -= $quantity;
        $asset->save();

        $user->balance -= $charge;
        $user->save();

        $recipientAsset = Asset::where('user_id', $recipientUser->id)->where('batch_id', $asset->batch_id)->first();

        if (!$recipientAsset) {
            $recipientAsset              = new Asset();
            $recipientAsset->user_id     = $recipientUser->id;
            $recipientAsset->batch_id    = $asset->batch_id;
        }

        $recipientAsset->quantity += $quantity;
        $recipientAsset->save();

        $trx = getTrx();

        $giftHistory               = new BeanHistory();
        $giftHistory->user_id      = $user->id;
        $giftHistory->asset_id     = $asset->id;
        $giftHistory->recipient_id = $recipientUser->id;
        $giftHistory->batch_id     = $asset->batch_id;
        $giftHistory->quantity    = $quantity;
        $giftHistory->item_unit_id = $asset->batch && $asset->batch->product ? $asset->batch->product->unit_id : null;
        $giftHistory->amount       = $request->amount;
        $giftHistory->currency_id  = $asset->batch && $asset->batch->product ? $asset->batch->product->currency_id : null;
        $giftHistory->charge       = $charge;
        $giftHistory->trx          = $trx;
        $giftHistory->type         = Status::GIFT_HISTORY;
        $giftHistory->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $charge;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Gift charge of Green Coffee to ' . $recipientUser->username;
        $transaction->trx          = $trx;
        $transaction->remark       = 'gift_bean';
        $transaction->save();

        notify($user, 'GIFT_BEAN', [
            'recipient_name' => $recipientUser->fullname,
            'category'       => $category->name,
            'quantity'       => showAmount($quantity, 4, currencyFormat: false),
            'amount'         => showAmount($request->amount),
            'charge'         => showAmount($charge),
            'trx'            => $trx,
        ]);

        notify($recipientUser, 'RECEIVED_GIFT_BEAN', [
            'user_name' => $user->fullname,
            'category'  => $category->name,
            'quantity'  => showAmount($quantity, 4, currencyFormat: false),
            'amount'    => showAmount($request->amount),
            'charge'    => showAmount($charge),
            'trx'       => $trx,
        ]);

        $notify[] = ['success', 'Bean gifted successfully'];
        return back()->withNotify($notify);
    }

    public function checkUser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user' => 'required',
        ]);

        if ($validation->fails()) {
            return responseError('validation_error', $validation->errors()->all());
        }

        $user = User::where('username', $request->user)->orWhere('email', $request->user)->first();

        if ($user) {
            return responseSuccess('valid_user', 'Valid user', ['user' => $user]);
        } else {
            return responseError('invalid_user', 'Invalid user');
        }
    }

    public function history()
    {
        $pageTitle     = 'Gift History';
        $giftHistories = BeanHistory::gift()->where('user_id', auth()->id())->with('batch.product')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.gift.history', compact('pageTitle', 'giftHistories'));
    }
}
