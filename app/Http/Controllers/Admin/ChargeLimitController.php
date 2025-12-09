<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChargeLimit;
use Illuminate\Http\Request;

class ChargeLimitController extends Controller
{
    public function create()
    {
        $pageTitle    = 'Manage Charge Limit';
        $chargeLimits = ChargeLimit::get();
        return view('admin.charge_limit.index', compact('pageTitle', 'chargeLimits'));
    }

    public function save(Request $request, $id)
    {
        $request->validate([
            'min_amount'     => 'required|numeric|gt:0',
            'max_amount'     => 'required|numeric|gt:min_amount',
            'fixed_charge'   => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'vat'            => 'required|numeric|min:0',
        ]);

        $chargeLimit                 = ChargeLimit::findOrFail($id);
        $chargeLimit->min_amount     = $request->min_amount;
        $chargeLimit->max_amount     = $request->max_amount;
        $chargeLimit->fixed_charge   = $request->fixed_charge;
        $chargeLimit->percent_charge = $request->percent_charge;
        $chargeLimit->vat            = $request->vat;
        $chargeLimit->save();

        $notify[] = ['success', 'Charge Limit updated successfully'];
        return back()->withNotify($notify);
    }
}
