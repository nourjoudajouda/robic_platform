<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
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
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
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
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'max_capacity_unit' => 'nullable|string|max:255',
            'max_capacity_kg' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $warehouse = new Warehouse();
        $warehouse->name = $request->name;
        $warehouse->location = $request->location;
        $warehouse->code = $this->generateCode();
        $warehouse->address = $request->address;
        $warehouse->latitude = $request->latitude;
        $warehouse->longitude = $request->longitude;
        $warehouse->manager_name = $request->manager_name;
        $warehouse->mobile = $request->mobile;
        $warehouse->max_capacity_unit = $request->max_capacity_unit;
        $warehouse->max_capacity_kg = $request->max_capacity_kg;
        $warehouse->area_sqm = $request->area_sqm;
        $warehouse->status = $request->status;
        $warehouse->save();

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
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'max_capacity_unit' => 'nullable|string|max:255',
            'max_capacity_kg' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . Status::ENABLE . ',' . Status::DISABLE,
        ]);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->name = $request->name;
        $warehouse->location = $request->location;
        // Code لا يتم تعديله - يتم إنشاؤه تلقائياً فقط عند الإضافة
        $warehouse->address = $request->address;
        $warehouse->latitude = $request->latitude;
        $warehouse->longitude = $request->longitude;
        $warehouse->manager_name = $request->manager_name;
        $warehouse->mobile = $request->mobile;
        $warehouse->max_capacity_unit = $request->max_capacity_unit;
        $warehouse->max_capacity_kg = $request->max_capacity_kg;
        $warehouse->area_sqm = $request->area_sqm;
        $warehouse->status = $request->status;
        $warehouse->save();

        $notify[] = ['success', 'Warehouse updated successfully'];
        return redirect()->route('admin.warehouse.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        $notify[] = ['success', 'Warehouse deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Warehouse::changeStatus($id);
    }
}

