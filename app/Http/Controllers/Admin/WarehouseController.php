<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Asset;
use App\Models\Batch;
use App\Models\BeanHistory;
use App\Constants\Status;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Warehouses';
        
        $query = Warehouse::query();
        
        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('location_en', 'like', "%{$search}%")
                  ->orWhere('location_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('manager_name_en', 'like', "%{$search}%")
                  ->orWhere('manager_name_ar', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
                // البحث في ID إذا كان البحث رقم
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }
        
        $warehouses = $query->orderBy('id', 'desc')->paginate(getPaginate());
        
        return view('admin.warehouse.index', compact('pageTitle', 'warehouses'));
    }

    public function create()
    {
        $pageTitle = 'Add Warehouse';
        return view('admin.warehouse.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'location_en' => 'required|string|max:255',
            'location_ar' => 'required|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_name_en' => 'required|string|max:255',
            'manager_name_ar' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'max_capacity_unit' => 'nullable|string|max:255',
            'max_capacity_kg' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $warehouse = new Warehouse();
        $warehouse->name = $request->name_en; // Keep for backward compatibility
        $warehouse->name_en = $request->name_en;
        $warehouse->name_ar = $request->name_ar;
        $warehouse->location = $request->location_en; // Keep for backward compatibility
        $warehouse->location_en = $request->location_en;
        $warehouse->location_ar = $request->location_ar;
        $warehouse->code = $this->generateCode();
        $warehouse->address = $request->address_en; // Keep for backward compatibility
        $warehouse->address_en = $request->address_en;
        $warehouse->address_ar = $request->address_ar;
        $warehouse->latitude = $request->latitude;
        $warehouse->longitude = $request->longitude;
        $warehouse->manager_name = $request->manager_name_en; // Keep for backward compatibility
        $warehouse->manager_name_en = $request->manager_name_en;
        $warehouse->manager_name_ar = $request->manager_name_ar;
        $warehouse->mobile = $request->mobile;
        $warehouse->max_capacity_unit = $request->max_capacity_unit;
        $warehouse->max_capacity_kg = $request->max_capacity_kg;
        $warehouse->area_sqm = $request->area_sqm;
        $warehouse->status = $request->status;
        $warehouse->save();

        $this->audit('create', 'تم إنشاء مستودع جديد: ' . $warehouse->name_en, $warehouse);

        $notify[] = ['success', 'Warehouse added successfully'];
        return redirect()->route('admin.warehouse.index')->withNotify($notify);
    }

    /**
     * Generate unique code automatically
     * Format: WH-XXX (WH for Warehouse + random number)
     */
    private function generateCode()
    {
        $prefix = 'WH'; // WH for Warehouse
        $code = '';
        
        do {
            $number = getNumber(3); // Generate 3-digit number
            $code = $prefix . '-' . $number;
        } while (Warehouse::where('code', $code)->exists()); // Check if code already exists
        
        return $code;
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Warehouse';
        $warehouse = Warehouse::findOrFail($id);
        return view('admin.warehouse.edit', compact('pageTitle', 'warehouse'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'location_en' => 'required|string|max:255',
            'location_ar' => 'required|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_name_en' => 'required|string|max:255',
            'manager_name_ar' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'max_capacity_unit' => 'nullable|string|max:255',
            'max_capacity_kg' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->name = $request->name_en; // Keep for backward compatibility
        $warehouse->name_en = $request->name_en;
        $warehouse->name_ar = $request->name_ar;
        $warehouse->location = $request->location_en; // Keep for backward compatibility
        $warehouse->location_en = $request->location_en;
        $warehouse->location_ar = $request->location_ar;
        // Code لا يتم تعديله - يتم إنشاؤه تلقائياً فقط عند الإضافة
        $warehouse->address = $request->address_en; // Keep for backward compatibility
        $warehouse->address_en = $request->address_en;
        $warehouse->address_ar = $request->address_ar;
        $warehouse->latitude = $request->latitude;
        $warehouse->longitude = $request->longitude;
        $warehouse->manager_name = $request->manager_name_en; // Keep for backward compatibility
        $warehouse->manager_name_en = $request->manager_name_en;
        $warehouse->manager_name_ar = $request->manager_name_ar;
        $warehouse->mobile = $request->mobile;
        $warehouse->max_capacity_unit = $request->max_capacity_unit;
        $warehouse->max_capacity_kg = $request->max_capacity_kg;
        $warehouse->area_sqm = $request->area_sqm;
        $warehouse->status = $request->status;
        $oldValues = $warehouse->getOriginal();
        $warehouse->save();
        $newValues = $warehouse->getChanges();

        $this->audit('update', 'تم تحديث المستودع: ' . $warehouse->name_en, $warehouse, $oldValues, $newValues);

        $notify[] = ['success', 'Warehouse updated successfully'];
        return redirect()->route('admin.warehouse.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouseName = $warehouse->name_en;
        $warehouse->delete();

        $this->audit('delete', 'تم حذف المستودع: ' . $warehouseName, $warehouse);

        $notify[] = ['success', 'Warehouse deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $oldStatus = $warehouse->status;
        $result = Warehouse::changeStatus($id);
        $warehouse->refresh();
        
        $statusText = $warehouse->status == \App\Constants\Status::ENABLE ? 'تفعيل' : 'تعطيل';
        $this->audit('status_change', "تم {$statusText} المستودع: " . $warehouse->name_en, $warehouse, ['status' => $oldStatus], ['status' => $warehouse->status]);
        
        return $result;
    }

    public function statistics($id)
    {
        $pageTitle = 'Warehouse Statistics';
        $warehouse = Warehouse::findOrFail($id);

        // 1. الكمية الإجمالية في المستودع (من جدول assets)
        $totalQuantity = Asset::where('warehouse_id', $warehouse->id)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        // 2. المعاملات الداخلة (شراء) مع التواريخ
        $incomingTransactions = BeanHistory::where('type', Status::BUY_HISTORY)
            ->where(function($query) use ($warehouse) {
                $query->whereHas('asset', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                })->orWhereHas('batch', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                });
            })
            ->with(['asset.product', 'asset.itemUnit', 'batch.product', 'batch.itemUnit', 'batch.warehouse', 'user', 'itemUnit', 'currency'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        // 3. المعاملات الصادرة (بيع واسترداد) مع التواريخ
        // نبحث في BeanHistory عن المعاملات التي asset_id يشير إلى assets في هذا المستودع
        // أو batch_id يشير إلى batches في هذا المستودع
        $outgoingTransactions = BeanHistory::whereIn('type', [Status::SELL_HISTORY, Status::REDEEM_HISTORY])
            ->where(function($query) use ($warehouse) {
                $query->whereHas('asset', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                })->orWhereHas('batch', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                });
            })
            ->with(['asset.product', 'asset.itemUnit', 'batch.product', 'batch.warehouse', 'user', 'itemUnit', 'currency', 'redeemData'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        // 4. جميع الـ Batches الموجودة في المستودع
        $batches = Batch::where('warehouse_id', $warehouse->id)
            ->with(['product', 'unit', 'itemUnit', 'currency', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        // 5. الكمية لكل مستخدم في هذا المستودع
        $userQuantities = Asset::where('warehouse_id', $warehouse->id)
            ->where('quantity', '>', 0)
            ->with('user', 'product', 'itemUnit')
            ->selectRaw('user_id, SUM(quantity) as total_quantity')
            ->groupBy('user_id')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function($item) use ($warehouse) {
                return [
                    'user' => $item->user,
                    'total_quantity' => $item->total_quantity,
                    'assets' => Asset::where('warehouse_id', $warehouse->id)
                        ->where('user_id', $item->user_id)
                        ->where('quantity', '>', 0)
                        ->with('product', 'itemUnit')
                        ->get()
                ];
            });

        return view('admin.warehouse.statistics', compact(
            'pageTitle', 
            'warehouse', 
            'totalQuantity', 
            'incomingTransactions',
            'outgoingTransactions',
            'batches',
            'userQuantities'
        ));
    }
}

