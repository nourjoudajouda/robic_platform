<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Shipping Methods';
        $shippingMethods = ShippingMethod::latest()->paginate(getPaginate());
        return view('admin.shipping_methods.index', compact('pageTitle', 'shippingMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = 'Add New Shipping Method';
        return view('admin.shipping_methods.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost_per_kg' => 'required|numeric|min:0',
        ]);

        $shippingMethod = new ShippingMethod();
        $shippingMethod->name = $request->name;
        $shippingMethod->cost_per_kg = $request->cost_per_kg;
        $shippingMethod->status = $request->status ? 1 : 0;
        $shippingMethod->save();

        $notify[] = ['success', 'Shipping method created successfully'];
        return redirect()->route('admin.shipping-methods.index')->withNotify($notify);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Shipping Method';
        $shippingMethod = ShippingMethod::findOrFail($id);
        return view('admin.shipping_methods.edit', compact('pageTitle', 'shippingMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost_per_kg' => 'required|numeric|min:0',
        ]);

        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->name = $request->name;
        $shippingMethod->cost_per_kg = $request->cost_per_kg;
        $shippingMethod->status = $request->status ? 1 : 0;
        $shippingMethod->save();

        $notify[] = ['success', 'Shipping method updated successfully'];
        return redirect()->route('admin.shipping-methods.index')->withNotify($notify);
    }

    /**
     * Update the status of the specified resource.
     */
    public function status($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->status = $shippingMethod->status == 1 ? 0 : 1;
        $shippingMethod->save();

        $notify[] = ['success', 'Shipping method status updated successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->delete();

        $notify[] = ['success', 'Shipping method deleted successfully'];
        return back()->withNotify($notify);
    }
}

