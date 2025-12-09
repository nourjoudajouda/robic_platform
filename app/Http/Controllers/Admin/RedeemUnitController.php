<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\RedeemUnit;
use Illuminate\Http\Request;

class RedeemUnitController extends Controller
{
    public function list()
    {
        $pageTitle   = 'Redeem Units';
        $redeemUnits = RedeemUnit::orderBy('type', 'asc')->orderBy('quantity', 'asc')->get();
        return view('admin.redeem_unit.list', compact('pageTitle', 'redeemUnits'));
    }

    public function save(Request $request, $id = 0)
    {
        $request->validate([
            'type'     => 'required|in:' . Status::REDEEM_UNIT_BAR . ',' . Status::REDEEM_UNIT_COIN,
            'quantity' => 'required|numeric|gt:0',
        ]);

        if ($id) {
            $redeemUnit = RedeemUnit::findOrFail($id);
            $notify[]   = ['success', 'Redeem unit updated successfully'];
        } else {
            $redeemUnit = new RedeemUnit();
            $notify[]   = ['success', 'Redeem unit added successfully'];
        }

        $redeemUnit->type     = $request->type;
        $redeemUnit->quantity = $request->quantity;
        $redeemUnit->save();

        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return RedeemUnit::changeStatus($id);
    }
}
