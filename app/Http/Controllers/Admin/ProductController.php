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
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
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
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $product = new Product();
        $product->name = $request->name_en; // Keep for backward compatibility
        $product->name_en = $request->name_en;
        $product->name_ar = $request->name_ar;
        $product->sku = $this->generateSku();
        $product->unit_id = $request->unit_id;
        $product->currency_id = $request->currency_id;
        $product->status = $request->status;
        $product->save();

        $this->audit('create', 'تم إنشاء منتج جديد: ' . $product->name_en, $product);

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
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $product = Product::findOrFail($id);
        $oldValues = $product->only(['name_en', 'name_ar', 'unit_id', 'currency_id', 'status']);
        $product->name = $request->name_en; // Keep for backward compatibility
        $product->name_en = $request->name_en;
        $product->name_ar = $request->name_ar;
        $product->unit_id = $request->unit_id;
        $product->currency_id = $request->currency_id;
        $product->status = $request->status;
        // SKU لا يتم تعديله - يتم إنشاؤه تلقائياً فقط عند الإضافة
        $product->save();

        $newValues = $product->only(['name_en', 'name_ar', 'unit_id', 'currency_id', 'status']);
        $this->audit('update', 'تم تحديث المنتج: ' . $product->name_en, $product, $oldValues, $newValues);

        $notify[] = ['success', 'Product updated successfully'];
        return redirect()->route('admin.product.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $productName = $product->name_en;
        $product->delete();

        $this->audit('delete', 'تم حذف المنتج: ' . $productName, $product);

        $notify[] = ['success', 'Product deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $product = Product::findOrFail($id);
        $oldStatus = $product->status;
        $result = Product::changeStatus($id);
        $product->refresh();
        
        $statusText = $product->status == \App\Constants\Status::ENABLE ? 'تفعيل' : 'تعطيل';
        $this->audit('status_change', "تم {$statusText} المنتج: " . $product->name_en, $product, ['status' => $oldStatus], ['status' => $product->status]);
        
        return $result;
    }
}
