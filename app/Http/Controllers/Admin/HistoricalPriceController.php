<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoricalPrice;
use Illuminate\Http\Request;

class HistoricalPriceController extends Controller
{
    public function list()
    {
        $pageTitle        = 'Historic Price';
        $historicalPrices = HistoricalPrice::orderBy('date', 'desc')->paginate(getPaginate());
        return view('admin.historical_price.list', compact('pageTitle', 'historicalPrices'));
    }

    public function save(Request $request, $id = 0)
    {
        $request->validate([
            'date'  => 'required|date',
            'price' => 'required|numeric|gt:0',
            'date'  => 'required|date|unique:historical_prices,date,' . $id,
        ]);

        if ($id) {
            $historicalPrice = HistoricalPrice::findOrFail($id);
            $notify[]        = ['success', 'Historical price updated successfully'];
        } else {
            $historicalPrice = new HistoricalPrice();
            $notify[]        = ['success', 'Historical price added successfully'];
        }


        $historicalPrice->date = $request->date;
        $historicalPrice->price = $request->price;
        $historicalPrice->save();

        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        HistoricalPrice::where('id',$id)->delete();
        $notify[] = ['success', 'Historical price deleted successfully'];
        return back()->withNotify($notify);
    }

}
