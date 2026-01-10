<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSellOrder;
use App\Models\Product;
use App\Models\User;
use App\Constants\Status;
use Illuminate\Http\Request;

class UserSellOrderController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'User Sell Orders';
        
        $query = UserSellOrder::with([
            'user',
            'product',
            'warehouse',
            'batch',
            'unit',
            'itemUnit',
            'currency',
            'asset'
        ]);
        
        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sell_order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('batch', function($q) use ($search) {
                      $q->where('batch_code', 'like', "%{$search}%");
                  });
                if (is_numeric($search)) {
                    $q->orWhere('id', $search)
                      ->orWhere('user_id', $search);
                }
            });
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by batch
        if ($request->has('batch_id') && $request->batch_id) {
            $query->where('batch_id', $request->batch_id);
        }
        
        $sellOrders = $query->orderBy('id', 'desc')->paginate(getPaginate());
        $products = Product::where('status', Status::ENABLE)->orderBy('name')->get();
        $users = User::orderBy('username')->limit(100)->get(); // Limit to avoid performance issues
        
        return view('admin.user-sell-order.index', compact('pageTitle', 'sellOrders', 'products', 'users'));
    }

    public function show($id)
    {
        $pageTitle = 'User Sell Order Details';
        $sellOrder = UserSellOrder::with([
            'user',
            'product',
            'warehouse',
            'batch',
            'unit',
            'itemUnit',
            'currency',
            'asset'
        ])->findOrFail($id);
        
        return view('admin.user-sell-order.show', compact('pageTitle', 'sellOrder'));
    }
}

