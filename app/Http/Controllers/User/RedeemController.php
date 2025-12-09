<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\ChargeLimit;
use App\Models\GoldHistory;
use App\Models\RedeemData;
use App\Models\RedeemUnit;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RedeemController extends Controller
{
    public function __construct()
    {
        if (!gs('redeem_option')) {
            abort(404);
        }
    }

    public function redeemForm()
    {
        $pageTitle   = 'Redeem Gold';
        $assets      = Asset::where('user_id', auth()->id())->get();
        $redeemUnits = RedeemUnit::active()->get();
        $chargeLimit = ChargeLimit::where('slug', 'redeem')->first();

        return view('Template::user.redeem.form', compact('pageTitle', 'assets', 'redeemUnits', 'chargeLimit'));
    }

    public function redeemStore(Request $request)
    {
        $request->validate([
            'asset_id'               => 'required|integer|gt:0',
            'redeem_unit_quantity'   => 'required|array',
            'redeem_unit_quantity.*' => 'required|integer|min:0',
        ]);

        $redeemUnitQuantity = array_filter($request->redeem_unit_quantity, function ($value) {
            return $value > 0;
        });

        $redeemUnitIds        = array_keys($redeemUnitQuantity);
        $redeemUnitQuantities = array_values($redeemUnitQuantity);

        $redeemUnits = RedeemUnit::active()->whereIn('id', $redeemUnitIds)->get();

        if (count($redeemUnits) != count($redeemUnitQuantities)) {
            $notify[] = ['error', 'Invalid redeem unit quantity'];
            return back()->withNotify($notify);
        }

        $totalQuantity = 0;
        $orderDetails  = [];

        foreach ($redeemUnits as $redeemUnit) {
            $totalQuantity += $redeemUnitQuantity[$redeemUnit->id] * $redeemUnit->quantity;
            $orderDetails[] = [
                'redeem_unit_id' => $redeemUnit->id,
                'type'           => $redeemUnit->type,
                'quantity'       => $redeemUnitQuantity[$redeemUnit->id],
                'text'           => ($redeemUnit->type == Status::REDEEM_UNIT_BAR ? 'Gold bar' : 'Gold coin') . ' - ' . showAmount($redeemUnit->quantity, currencyFormat: false) . ' gram (' . $redeemUnitQuantity[$redeemUnit->id] . ' pieces)',
            ];
        }

        $orderDetails = collect($orderDetails)->sortBy('type')->values()->toArray();

        $user  = auth()->user();
        $asset = Asset::with('category')->where('user_id', $user->id)->findOrFail($request->asset_id);

        if ($asset->quantity < $totalQuantity) {
            $notify[] = ['error', 'Insufficient gold asset'];
            return back()->withNotify($notify);
        }

        $chargeLimit = ChargeLimit::where('slug', 'redeem')->first();
        $category    = $asset->category;
        $amount      = $category->price * $totalQuantity;

        if ($chargeLimit->min_amount > $amount) {
            $notify[] = ['error', 'The minimum redeem amount is ' . showAmount($chargeLimit->min_amount)];
            return back()->withNotify($notify);
        }

        if ($chargeLimit->max_amount < $amount) {
            $notify[] = ['error', 'The maximum redeem amount is ' . showAmount($chargeLimit->max_amount)];
            return back()->withNotify($notify);
        }

        $charge = $chargeLimit->fixed_charge + ($amount * $chargeLimit->percent_charge / 100);

        if ($user->balance < $charge) {
            $notify[] = ['error', 'Insufficient balance for charge'];
            return back()->withNotify($notify);
        }

        $redeemData = [
            'asset_id'       => $asset->id,
            'amount'         => $amount,
            'total_quantity' => $totalQuantity,
            'charge'         => $charge,
            'order_details'  => $orderDetails,
        ];

        session()->put('redeem_data', (object) $redeemData);
        return to_route('user.redeem.address');
    }

    public function address()
    {
        $pageTitle = 'Redeem Gold - Address';
        $redeemData = session()->get('redeem_data');

        if (!$redeemData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }

        return view('Template::user.redeem.address', compact('pageTitle','redeemData'));
    }

    public function addressStore(Request $request)
    {
        $redeemData = session()->get('redeem_data');

        if (!$redeemData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }

        $request->validate([
            'address' => 'required|string',
        ]);        

        $user  = auth()->user();
        $asset = Asset::findOrFail($redeemData->asset_id);

        if ($asset->quantity < $redeemData->total_quantity) {
            session()->forget('redeem_data');
            $notify[] = ['error', 'Insufficient gold asset'];
            return back()->withNotify($notify);
        }

        if ($user->balance < $redeemData->charge) {
            session()->forget('redeem_data');
            $notify[] = ['error', 'Insufficient balance for charge'];
            return back()->withNotify($notify);
        }

        $asset->quantity -= $redeemData->total_quantity;
        $asset->save();

        $user->balance -= $redeemData->charge;
        $user->save();

        $trx = getTrx();

        $redeemHistory              = new GoldHistory();
        $redeemHistory->user_id     = $user->id;
        $redeemHistory->asset_id    = $asset->id;
        $redeemHistory->category_id = $asset->category_id;
        $redeemHistory->amount      = $redeemData->amount;
        $redeemHistory->quantity    = $redeemData->total_quantity;
        $redeemHistory->charge      = $redeemData->charge;
        $redeemHistory->trx         = $trx;
        $redeemHistory->type        = Status::REDEEM_HISTORY;
        $redeemHistory->save();

        $orderData = [
            'asset_id' => $asset->id,
            'items'    => $redeemData->order_details,
        ];

        $address = 'Address: ' . $request->address;
        $address .= $request->city ? ', City: ' . $request->city : '';
        $address .= $request->state ? ', State: ' . $request->state : '';
        $address .= $request->zip_code ? ', Zip Code: ' . $request->zip_code : '';

        $redeemDataLog                   = new RedeemData();
        $redeemDataLog->gold_history_id  = $redeemHistory->id;
        $redeemDataLog->delivery_address = $address;
        $redeemDataLog->order_details    = $orderData;
        $redeemDataLog->status           = Status::REDEEM_STATUS_PROCESSING;
        $redeemDataLog->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $redeemData->charge;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Redeem ' . $asset->category->name;
        $transaction->trx          = $trx;
        $transaction->remark       = 'redeem_gold';
        $transaction->save();

        $sentence = collect($orderData['items'])->pluck('text')->toArray();
        $sentence = count($sentence) > 1 ? implode(', ', array_slice($sentence, 0, -1)) . ' and ' . end($sentence) : $sentence[0];

        notify($user, 'REDEEM_GOLD', [
            'category' => $asset->category->name,
            'quantity' => showAmount($redeemHistory->total_quantity, 4, currencyFormat: false),
            'amount'   => showAmount($redeemHistory->amount),
            'charge'   => showAmount($redeemHistory->charge),
            'trx'      => $transaction->trx,
            'details'  => $sentence,
        ]);

        $notify[] = ['success', 'Gold redeemed successfully'];
        return to_route('user.redeem.success.page')->withNotify($notify)->with('redeem_history', $redeemHistory);
    }

    public function successPage()
    {
        $pageTitle     = 'Redeem Success';
        $redeemHistory = session()->get('redeem_history');
        $redeemHistory = GoldHistory::latest()->first();

        if (!$redeemHistory) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }
        return view('Template::user.redeem.success', compact('pageTitle', 'redeemHistory'));
    }

    public function history()
    {
        $pageTitle       = 'Redeem History';
        $redeemHistories = GoldHistory::redeem()->where('user_id', auth()->id())->with('category')->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.redeem.history', compact('pageTitle', 'redeemHistories'));
    }

}
