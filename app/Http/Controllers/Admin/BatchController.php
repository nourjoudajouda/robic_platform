<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Unit;
use App\Models\Currency;
use App\Constants\Status;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Batches';
        
        $query = Batch::with(['product', 'warehouse', 'unit', 'currency']);
        
        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('warehouse', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
                // البحث في ID إذا كان البحث رقم
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }
        
        $batches = $query->orderBy('id', 'desc')->paginate(getPaginate());
        
        return view('admin.batch.index', compact('pageTitle', 'batches'));
    }

    public function create()
    {
        $pageTitle = 'Add Batch';
        $products = Product::with(['unit', 'currency'])->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        
        return view('admin.batch.create', compact('pageTitle', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'units_count' => 'required|numeric|min:0',
            'quality_grade' => 'nullable|string|max:255',
            'quality_grade_custom' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'exp_date' => 'nullable|date',
            'buy_price' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        // جلب المنتج للحصول على unit_id و currency_id
        $product = Product::findOrFail($request->product_id);
        if (!$product->unit_id || !$product->currency_id) {
            $notify[] = ['error', 'Product must have unit and currency set'];
            return back()->withNotify($notify)->withInput();
        }

        $batch = new Batch();
        $batch->product_id = $request->product_id;
        $batch->warehouse_id = $request->warehouse_id;
        $batch->units_count = $request->units_count;
        $batch->unit_id = $product->unit_id; // من المنتج
        $batch->currency_id = $product->currency_id; // من المنتج
        $batch->batch_code = $this->generateBatchCode();
        // Handle quality_grade (if "Other" is selected, use custom value)
        $batch->quality_grade = ($request->quality_grade === 'Other' && $request->quality_grade_custom) 
            ? $request->quality_grade_custom 
            : $request->quality_grade;
        $batch->origin_country = $request->origin_country;
        $batch->exp_date = $request->exp_date;
        $batch->buy_price = $request->buy_price;
        
        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = getFilePath('batchAttachment');
            $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if doesn't exist
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0755, true);
            }
            
            $file->move(public_path($path), $filename);
            $batch->attachment = $filename;
        }
        
        $batch->status = $request->status;
        $batch->save();

        // تحديث سعر السوق للمنتج
        Batch::updateMarketPrice($batch->product_id);

        $this->audit('create', 'تم إنشاء دفعة جديدة: ' . $batch->batch_code, $batch);

        $notify[] = ['success', 'Batch added successfully'];
        return redirect()->route('admin.batch.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Batch';
        $batch = Batch::with(['product.unit', 'product.currency'])->findOrFail($id);
        $products = Product::with(['unit', 'currency'])->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        
        return view('admin.batch.edit', compact('pageTitle', 'batch', 'products', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'units_count' => 'required|numeric|min:0',
            'quality_grade' => 'nullable|string|max:255',
            'quality_grade_custom' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'exp_date' => 'nullable|date',
            'buy_price' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        // جلب المنتج للحصول على unit_id و currency_id
        $product = Product::findOrFail($request->product_id);
        if (!$product->unit_id || !$product->currency_id) {
            $notify[] = ['error', 'Product must have unit and currency set'];
            return back()->withNotify($notify)->withInput();
        }

        $batch = Batch::findOrFail($id);
        $batch->product_id = $request->product_id;
        $batch->warehouse_id = $request->warehouse_id;
        $batch->units_count = $request->units_count;
        $batch->unit_id = $product->unit_id; // من المنتج
        $batch->currency_id = $product->currency_id; // من المنتج
        // batch_code لا يتم تعديله
        // Handle quality_grade (if "Other" is selected, use custom value)
        $batch->quality_grade = ($request->quality_grade === 'Other' && $request->quality_grade_custom) 
            ? $request->quality_grade_custom 
            : $request->quality_grade;
        $batch->origin_country = $request->origin_country;
        $batch->exp_date = $request->exp_date;
        $batch->buy_price = $request->buy_price;
        
        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($batch->attachment && file_exists(public_path(getFilePath('batchAttachment') . '/' . $batch->attachment))) {
                unlink(public_path(getFilePath('batchAttachment') . '/' . $batch->attachment));
            }
            
            $file = $request->file('attachment');
            $path = getFilePath('batchAttachment');
            $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if doesn't exist
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0755, true);
            }
            
            $file->move(public_path($path), $filename);
            $batch->attachment = $filename;
        }
        
        $batch->status = $request->status;
        $batch->save();

        // تحديث سعر السوق للمنتج
        Batch::updateMarketPrice($batch->product_id);

        $oldValues = $batch->getOriginal();
        $batch->save();
        $newValues = $batch->getChanges();

        $this->audit('update', 'تم تحديث الدفعة: ' . $batch->batch_code, $batch, $oldValues, $newValues);

        $notify[] = ['success', 'Batch updated successfully'];
        return redirect()->route('admin.batch.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $batch = Batch::findOrFail($id);
        $batchCode = $batch->batch_code;
        $productId = $batch->product_id;
        $batch->delete();

        // تحديث سعر السوق للمنتج بعد الحذف
        Batch::updateMarketPrice($productId);

        $this->audit('delete', 'تم حذف الدفعة: ' . $batchCode, $batch);

        $notify[] = ['success', 'Batch deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $batch = Batch::findOrFail($id);
        $oldStatus = $batch->status;
        $result = Batch::changeStatus($id);
        $batch->refresh();
        
        $statusText = $batch->status == \App\Constants\Status::ENABLE ? 'تفعيل' : 'تعطيل';
        $this->audit('status_change', "تم {$statusText} الدفعة: " . $batch->batch_code, $batch, ['status' => $oldStatus], ['status' => $batch->status]);
        
        return $result;
    }

    /**
     * Generate unique batch code automatically
     * Format: BT-XXX (BT for Batch + random number)
     */
    private function generateBatchCode()
    {
        $prefix = 'BT'; // BT for Batch
        $code = '';
        
        do {
            $number = getNumber(3); // Generate 3-digit number
            $code = $prefix . '-' . $number;
        } while (Batch::where('batch_code', $code)->exists()); // Check if code already exists
        
        return $code;
    }
}

