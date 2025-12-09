<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\ChargeLimit;
use App\Models\GoldHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SellController extends Controller
{
    public function sellForm()
    {
        $pageTitle   = "Sell Gold";
        $assets      = Asset::where('user_id', '=', auth()->id())->get();
        $chargeLimit = ChargeLimit::where('slug', 'sell')->first();

        return view('Template::user.sell.form', compact('pageTitle', 'assets', 'chargeLimit'));
    }

    public function sellSubmit(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'amount'   => 'required|numeric|gt:0',
            'quantity' => 'required|numeric|gt:0',
        ]);

        $user  = auth()->user();
        $asset = Asset::where('user_id', $user->id)->findOrFail($request->asset_id);

        $category = $asset->category;
        $quantity = $request->amount / $category->price;
        $amount   = $request->amount;

        if ($asset->quantity < $quantity) {
            $notify[] = ['error', 'Insufficient gold asset'];
            return back()->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'sell')->first();

        if ($chargeLimit->min_amount > $amount) {
            $notify[] = ['error', 'The minimum sell amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $amount) {
            $notify[] = ['error', 'The maximum sell amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $charge      = $chargeLimit->fixed_charge + $amount * $chargeLimit->percent_charge / 100;
        $finalAmount = $amount - $charge;

        $sellData = [
            'asset_id'      => $asset->id,
            'quantity'      => $quantity,
            'amount'        => $amount,
            'charge'        => $charge,
            'final_amount'  => $finalAmount,
            'current_asset' => $asset->quantity,
            'net_asset'     => $asset->quantity - $quantity,
        ];

        session()->put('sell_data', (object) $sellData);
        return to_route('user.sell.preview');
    }

    public function preview()
    {
        $pageTitle = 'Sell Preview';
        $sellData  = session('sell_data');
        if (!$sellData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.sell.form')->withNotify($notify);
        }

        return view('Template::user.sell.preview', compact('pageTitle', 'sellData'));
    }

    public function sellStore(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'amount'   => 'required|numeric|gt:0',
        ]);

        $user  = auth()->user();
        $asset = Asset::where('user_id', $user->id)->findOrFail($request->asset_id);

        $category = $asset->category;
        $quantity = $request->amount / $category->price;
        $amount   = $request->amount;

        if ($asset->quantity < $quantity) {
            $notify[] = ['error', 'Insufficient gold asset'];
            return to_route('user.sell.form')->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'sell')->first();

        if ($chargeLimit->min_amount > $amount) {
            $notify[] = ['error', 'The minimum sell amount is ' . showAmount($chargeLimit->min_amount)];
            return to_route('user.sell.form')->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $amount) {
            $notify[] = ['error', 'The maximum sell amount is ' . showAmount($chargeLimit->max_amount)];
            return to_route('user.sell.form')->withNotify($notify);
        }


        $charge      = $chargeLimit->fixed_charge + $amount * $chargeLimit->percent_charge / 100;
        $finalAmount = $amount - $charge;

        $asset->quantity -= $quantity;
        $asset->save();

        $user->balance += $finalAmount;
        $user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $finalAmount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = $charge;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Sell ' . $category->name;
        $transaction->trx          = getTrx();
        $transaction->remark       = 'sell_gold';
        $transaction->save();

        $sellHistory              = new GoldHistory();
        $sellHistory->user_id     = $user->id;
        $sellHistory->asset_id    = $asset->id;
        $sellHistory->category_id = $category->id;
        $sellHistory->quantity    = $quantity;
        $sellHistory->amount      = $amount;
        $sellHistory->charge      = $charge;
        $sellHistory->trx         = $transaction->trx;
        $sellHistory->type        = Status::SELL_HISTORY;
        $sellHistory->save();

        notify($user, 'SELL_GOLD', [
            'category' => $category->name,
            'quantity' => showAmount($quantity, 4, currencyFormat: false),
            'amount'   => showAmount($amount),
            'charge'   => showAmount($charge),
            'trx'      => $transaction->trx,
        ]);

        $notify[] = ['success', 'Gold asset sold successfully'];
        return to_route('user.sell.success')->withNotify($notify)->with('sell_history', $sellHistory);
    }

    public function successPage()
    {
        $pageTitle   = 'Gold Sold';
        $sellHistory = session('sell_history');
        if (!$sellHistory) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.sell.history')->withNotify($notify);
        }

        return view('Template::user.sell.success', compact('pageTitle', 'sellHistory'));
    }

    public function history()
    {
        $pageTitle     = 'Sell History';
        $sellHistories = GoldHistory::sell()->where('user_id', auth()->id())->with('category')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.sell.history', compact('pageTitle', 'sellHistories'));
    }
}
