<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;
use App\Models\Asset;
use App\Models\Category;
use App\Models\ChargeLimit;
use App\Models\GatewayCurrency;
use App\Models\GoldHistory;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    public function buyForm()
    {
        $pageTitle   = 'Buy Gold';
        $categories  = Category::active()->get();
        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();
        return view('Template::user.buy.form', compact('pageTitle', 'categories', 'chargeLimit'));
    }

    public function buyStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'amount'      => 'required|numeric|gt:0',
        ]);

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();

        if ($chargeLimit->min_amount > $request->amount) {
            $notify[] = ['error', 'The minimum buy amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $request->amount) {
            $notify[] = ['error', 'The maximum buy amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $category    = Category::active()->findOrFail($request->category_id);
        $quantity    = $request->amount / $category->price;
        $charge      = $chargeLimit->fixed_charge + $chargeLimit->percent_charge * $request->amount / 100;
        $vat         = $request->amount * $chargeLimit->vat / 100;
        $totalAmount = $request->amount + $charge + $vat;

        $buyData = [
            'category_id'  => $category->id,
            'amount'       => $request->amount,
            'quantity'     => getAmount($quantity, 8),
            'charge'       => $charge,
            'vat'          => $vat,
            'total_amount' => $totalAmount,
        ];

        session()->put('buy_data', (object) $buyData);

        return to_route('user.buy.payment.form');
    }

    public function paymentForm(Request $request)
    {
        $pageTitle = 'Buy Gold';
        $buyData   = session('buy_data');

        if (!$buyData) {
            $notify[] = ['error', 'Invalid session'];
            return redirect()->route('user.buy.form')->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();

        return view('Template::user.buy.payment_form', compact('pageTitle', 'gatewayCurrency', 'buyData', 'chargeLimit'));
    }

    public function paymentSubmit(Request $request)
    {
        $buyData = session('buy_data');

        if (!$buyData) {
            $notify[] = ['error', 'Invalid session'];
            return redirect()->route('user.buy.form')->withNotify($notify);
        }

        $request->validate([
            'gateway'     => 'required',
            'currency'    => 'required',
            'category_id' => 'required|integer',
        ]);

        if ($request->gateway != 'main') {
            $gate = GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

            if (!$gate) {
                $notify[] = ['error', 'Invalid gateway'];
                return back()->withNotify($notify);
            }
        }

        $chargeLimit = ChargeLimit::where('slug', 'buy')->first();

        $vat         = $buyData->amount * $chargeLimit->vat / 100;
        $charge      = $chargeLimit->fixed_charge + $chargeLimit->percent_charge * $buyData->amount / 100;
        $totalAmount = $buyData->amount + $charge + $vat;

        $user     = auth()->user();
        $category = Category::active()->findOrFail($request->category_id);
        $quantity = $buyData->amount / $category->price;

        if ($request->gateway == 'main') {
            if ($totalAmount > $user->balance) {
                $notify[] = ['error', 'Insufficient balance'];
                return back()->withNotify($notify);
            }

            $buyHistory = Asset::buyGold($user, $category, $buyData->amount, $totalAmount, $quantity, $charge, $vat);

            $notify[] = ['success', 'Gold purchased successfully'];
            return to_route('user.buy.success')->withNotify($notify)->with('buy_history', $buyHistory);
        }

        if ($gate->min_amount > $totalAmount || $gate->max_amount < $totalAmount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $buyInfo = [
            'data'  => [
                'amount'   => $buyData->amount,
                'quantity' => $quantity,
                'charge'   => $charge,
                'vat'      => $vat,
            ],
            'other' => [
                'category_id' => $category->id,
                'success_url' => route('user.buy.success'),
                'failed_url'  => route('user.buy.form'),
            ],
        ];

        PaymentController::insertDeposit($gate, $totalAmount, $buyInfo);
        return to_route('user.deposit.confirm');
    }

    public function successPage()
    {
        $pageTitle  = 'Buy Gold';
        // $buyHistory = session()->get('buy_history');
        // $previousUrl = url()->previous();
        // if ($previousUrl == route('user.deposit.confirm')) {
        //     $buyHistory = GoldHistory::buy()->where('user_id', auth()->id())->orderBy('id', 'desc')->first();
        // }
        
        // if (!$buyHistory) {
        //     $notify[] = ['error', 'Invalid session data'];
        //     return to_route('user.buy.history')->withNotify($notify);
        // }
        $buyHistory = GoldHistory::buy()->where('user_id', auth()->id())->orderBy('id', 'desc')->first();
        return view('Template::user.buy.success', compact('pageTitle', 'buyHistory'));
    }

    public function history()
    {
        $pageTitle    = 'Buy History';
        $buyHistories = GoldHistory::buy()->where('user_id', auth()->id())->with('category')->orderBy('id', 'desc')->paginate(getPaginate());
        $vat          = $buyHistories->sum('vat');
        return view('Template::user.buy.history', compact('pageTitle', 'buyHistories', 'vat'));
    }

}
