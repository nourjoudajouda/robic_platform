<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Constants\Status;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Products';
        
        $query = Product::query();
        
        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
                // البحث في ID إذا كان البحث رقم
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }
        
        $products = $query->orderBy('id', 'desc')->paginate(getPaginate());
        
        return view('admin.product.index', compact('pageTitle', 'products'));
    }

    public function create()
    {
        $pageTitle = 'Add Product';
        $units = \App\Models\Unit::orderBy('name')->get();
        $currencies = \App\Models\Currency::orderBy('name')->get();
        return view('admin.product.create', compact('pageTitle', 'units', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->sku = $this->generateSku();
        $product->unit_id = $request->unit_id;
        $product->currency_id = $request->currency_id;
        $product->status = $request->status;
        $product->save();

            $notify[] = ['success', 'Product added successfully'];
        return redirect()->route('admin.product.index')->withNotify($notify);
    }

    /**
     * Generate unique SKU automatically
     * Format: RO-XXX (first 2 letters of system name + random number)
     */
    private function generateSku()
    {
        $prefix = 'RO'; // First 2 letters of "robic"
        $sku = '';
        
        do {
            $number = getNumber(3); // Generate 3-digit number
            $sku = $prefix . '-' . $number;
        } while (Product::where('sku', $sku)->exists()); // Check if SKU already exists
        
        return $sku;
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Product';
        $product = Product::with(['unit', 'currency'])->findOrFail($id);
        $units = \App\Models\Unit::orderBy('name')->get();
        $currencies = \App\Models\Currency::orderBy('name')->get();
        return view('admin.product.edit', compact('pageTitle', 'product', 'units', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->unit_id = $request->unit_id;
        $product->currency_id = $request->currency_id;
        $product->status = $request->status;
        // SKU لا يتم تعديله - يتم إنشاؤه تلقائياً فقط عند الإضافة
        $product->save();

        $notify[] = ['success', 'Product updated successfully'];
        return redirect()->route('admin.product.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $notify[] = ['success', 'Product deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Product::changeStatus($id);
    }
}
