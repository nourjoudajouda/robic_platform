<?php

namespace App\Http\Controllers\Admin;

use App\Models\PriceApi;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceApiController extends Controller
{
    public function list()
    {
        $pageTitle = 'Api List';
        $apis = PriceApi::get();       
        return view('admin.price_api.list', compact('pageTitle', 'apis'));
    }

    public function update(Request $request, $id)
    {

        $api       = PriceApi::findOrFail($id);
        $validationRule = [];

        foreach ($api->configuration as $key => $val) {
            $validationRule = array_merge($validationRule, [$key => 'required']);
        }
        $request->validate($validationRule);

        $configurations = json_decode(json_encode($api->configuration), true);

        foreach ($configurations as $key => $value) {
            $configurations[$key]['value'] = $request->$key;
        }

        $api->configuration = $configurations;
        $api->save();


        $notify[] = ['success', "Configuration updated successfully"];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $priceApi = PriceApi::findOrFail($id);
        $priceApi->status = !$priceApi->status;
        $priceApi->save();

        if($priceApi->status){
            PriceApi::where('id', '!=', $id)->update(['status' => Status::DISABLE]);
        }

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}