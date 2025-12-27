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
        $chargeLimits = ChargeLimit::where('slug', '!=', 'gift')->get();
        return view('admin.charge_limit.index', compact('pageTitle', 'chargeLimits'));
    }

    public function save(Request $request, $id)
    {
        $chargeLimit = ChargeLimit::findOrFail($id);
        
        $request->validate([
            'fixed_charge'   => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'vat'            => 'required|numeric|min:0',
            'pickup_fee'     => 'nullable|numeric|min:0',
        ]);

        $chargeLimit->fixed_charge   = $request->fixed_charge;
        $chargeLimit->percent_charge = $request->percent_charge;
        $chargeLimit->vat            = $request->vat;
        
        if ($chargeLimit->slug === 'redeem') {
            $chargeLimit->pickup_fee = $request->pickup_fee ?? 0;
        }
        
        $chargeLimit->save();

        $notify[] = ['success', 'Charge Limit updated successfully'];
        return back()->withNotify($notify);
    }
}
