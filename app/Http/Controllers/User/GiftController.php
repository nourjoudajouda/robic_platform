<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\ChargeLimit;
use App\Models\GoldHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiftController extends Controller
{
    public function giftForm()
    {
        $pageTitle   = 'Gift Form';
        $assets      = Asset::where('user_id', '=', auth()->id())->get();
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

        $asset    = Asset::where('user_id', $user->id)->findOrFail($request->asset_id);
        $category = Category::find($asset->category_id);
        $quantity = $request->amount / $category->price;

        if ($quantity > $asset->quantity) {
            $notify[] = ['error', 'Insufficient gold asset'];
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

        $recipientAsset = Asset::where('user_id', $recipientUser->id)->where('category_id', $category->id)->first();

        if (!$recipientAsset) {
            $recipientAsset              = new Asset();
            $recipientAsset->user_id     = $recipientUser->id;
            $recipientAsset->category_id = $category->id;
        }

        $recipientAsset->quantity += $quantity;
        $recipientAsset->save();

        $trx = getTrx();

        $giftHistory               = new GoldHistory();
        $giftHistory->user_id      = $user->id;
        $giftHistory->asset_id     = $asset->id;
        $giftHistory->recipient_id = $recipientUser->id;
        $giftHistory->category_id  = $category->id;
        $giftHistory->quantity     = $quantity;
        $giftHistory->amount       = $request->amount;
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
        $transaction->details      = 'Gift charge of ' . $category->name . ' to ' . $recipientUser->username;
        $transaction->trx          = $trx;
        $transaction->remark       = 'gift_gold';
        $transaction->save();

        notify($user, 'GIFT_GOLD', [
            'recipient_name' => $recipientUser->fullname,
            'category'       => $category->name,
            'quantity'       => showAmount($quantity, 4, currencyFormat: false),
            'amount'         => showAmount($request->amount),
            'charge'         => showAmount($charge),
            'trx'            => $trx,
        ]);

        notify($recipientUser, 'RECEIVED_GIFT_GOLD', [
            'user_name' => $user->fullname,
            'category'  => $category->name,
            'quantity'  => showAmount($quantity, 4, currencyFormat: false),
            'amount'    => showAmount($request->amount),
            'charge'    => showAmount($charge),
            'trx'       => $trx,
        ]);

        $notify[] = ['success', 'Gold gifted successfully'];
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
        $giftHistories = GoldHistory::gift()->where('user_id', auth()->id())->with('category')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.gift.history', compact('pageTitle', 'giftHistories'));
    }
}
