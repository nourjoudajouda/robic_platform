<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchSellOrder;
use App\Models\Batch;
use App\Constants\Status;
use Illuminate\Http\Request;

class BatchSellOrderController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Batch Sell Orders';
        
        $query = BatchSellOrder::with(['batch.product', 'batch.warehouse', 'product', 'warehouse', 'unit', 'currency']);
        
        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sell_order_code', 'like', "%{$search}%")
                  ->orWhereHas('batch', function($q) use ($search) {
                      $q->where('batch_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }
        
        // Filter by batch
        if ($request->has('batch_id') && $request->batch_id) {
            $query->where('batch_id', $request->batch_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        $sellOrders = $query->orderBy('id', 'desc')->paginate(getPaginate());
        $batches = Batch::where('status', Status::ENABLE)->orderBy('batch_code')->get();
        
        return view('admin.batch-sell-order.index', compact('pageTitle', 'sellOrders', 'batches'));
    }

    public function create()
    {
        $pageTitle = 'Add Batch Sell Order';
        $batches = Batch::where('status', Status::ENABLE)
            ->with(['product.unit', 'product.currency', 'warehouse', 'unit', 'currency'])
            ->orderBy('batch_code')
            ->get();
        
        // حساب الكمية المتاحة لكل batch (fresh calculation)
        // نستخدم query مباشرة للحصول على أحدث البيانات من قاعدة البيانات
        $availableQuantities = [];
        foreach ($batches as $batch) {
            // حساب مباشر من قاعدة البيانات بدون استخدام cache
            $batchTotalQuantity = $batch->units_count ?? 0;
            
            // حساب الكمية المستخدمة من batch_sell_orders نشطة باستخدام DB facade مباشرة
            $usedQuantity = \Illuminate\Support\Facades\DB::table('batch_sell_orders')
                ->where('batch_id', $batch->id)
                ->where('status', Status::SELL_ORDER_ACTIVE)
                ->sum('quantity');
            
            // الكمية المتاحة = الكمية الإجمالية - الكمية المستخدمة
            $availableQuantities[$batch->id] = max(0, $batchTotalQuantity - ($usedQuantity ?? 0));
        }
        
        return view('admin.batch-sell-order.create', compact('pageTitle', 'batches', 'availableQuantities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'quantity' => 'required|numeric|min:0',
            'available_quantity' => 'nullable|numeric|min:0|max:' . $request->quantity,
            'sell_price' => 'required|numeric|min:0',
            'status' => 'required|in:' . Status::SELL_ORDER_ACTIVE . ',' . Status::SELL_ORDER_INACTIVE,
        ]);

        $batch = Batch::with(['product.unit', 'product.currency', 'warehouse', 'unit', 'currency'])->findOrFail($request->batch_id);
        
        // التحقق من أن المنتج لديه unit_id و currency_id
        if (!$batch->product->unit_id || !$batch->product->currency_id) {
            $notify[] = ['error', 'Product must have unit and currency set'];
            return back()->withNotify($notify)->withInput();
        }
        
        // التحقق من أن الكمية لا تتجاوز الكمية المتاحة للباتش
        $availableBatchQuantity = $batch->getAvailableQuantityForSellOrder();
        $requestedQuantity = $request->quantity;
        
        if ($requestedQuantity > $availableBatchQuantity) {
            $notify[] = ['error', 'The requested quantity exceeds the available batch quantity. Available: ' . showAmount($availableBatchQuantity, 4, currencyFormat: false) . ' ' . ($batch->product->unit->symbol ?? 'Unit')];
            return back()->withNotify($notify)->withInput();
        }
        
        if ($availableBatchQuantity <= 0) {
            $notify[] = ['error', 'No available quantity in this batch. All quantity is already in sell orders.'];
            return back()->withNotify($notify)->withInput();
        }

        $sellOrder = new BatchSellOrder();
        $sellOrder->batch_id = $request->batch_id;
        $sellOrder->product_id = $batch->product_id;
        $sellOrder->warehouse_id = $batch->warehouse_id;
        $sellOrder->unit_id = $batch->product->unit_id; // من المنتج
        $sellOrder->currency_id = $batch->product->currency_id; // من المنتج
        $sellOrder->quantity = $request->quantity;
        $sellOrder->available_quantity = $request->quantity; // نفس الكمية المعروضة للبيع
        $sellOrder->sell_price = $request->sell_price;
        $sellOrder->sell_order_code = BatchSellOrder::generateSellOrderCode();
        $sellOrder->status = $request->status == 1 ? Status::SELL_ORDER_ACTIVE : Status::SELL_ORDER_INACTIVE;
        $sellOrder->save();

        // تحديث سعر السوق للمنتج
        Batch::updateMarketPrice($batch->product_id);

        $notify[] = ['success', 'Batch sell order added successfully'];
        return redirect()->route('admin.batch-sell-order.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Batch Sell Order';
        $sellOrder = BatchSellOrder::with(['batch.product.unit', 'batch.product.currency', 'batch.warehouse', 'batch.unit', 'batch.currency'])->findOrFail($id);
        $batches = Batch::where('status', Status::ENABLE)
            ->with(['product.unit', 'product.currency', 'warehouse', 'unit', 'currency'])
            ->orderBy('batch_code')
            ->get();
        
        // حساب الكمية المتاحة لكل batch
        $availableQuantities = [];
        foreach ($batches as $batch) {
            // عند التعديل، نضيف الكمية الحالية للسجل الحالي إذا كان نفس الباتش
            if ($sellOrder->batch_id == $batch->id) {
                $currentUsedQuantity = BatchSellOrder::where('batch_id', $batch->id)
                    ->where('status', Status::SELL_ORDER_ACTIVE)
                    ->where('id', '!=', $sellOrder->id)
                    ->sum('quantity');
                $availableQuantities[$batch->id] = ($batch->units_count - $currentUsedQuantity);
            } else {
                $availableQuantities[$batch->id] = $batch->getAvailableQuantityForSellOrder();
            }
        }
        
        return view('admin.batch-sell-order.edit', compact('pageTitle', 'sellOrder', 'batches', 'availableQuantities'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'quantity' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'status' => 'required|in:' . Status::SELL_ORDER_ACTIVE . ',' . Status::SELL_ORDER_INACTIVE,
        ]);

        $sellOrder = BatchSellOrder::findOrFail($id);
        $batch = Batch::with(['product.unit', 'product.currency', 'warehouse', 'unit', 'currency'])->findOrFail($request->batch_id);
        
        // التحقق من أن المنتج لديه unit_id و currency_id
        if (!$batch->product->unit_id || !$batch->product->currency_id) {
            $notify[] = ['error', 'Product must have unit and currency set'];
            return back()->withNotify($notify)->withInput();
        }
        
        // حساب الكمية المتاحة (الكمية الإجمالية - الكمية المستخدمة في sell orders أخرى + الكمية الحالية لهذا السجل)
        $batchTotalQuantity = $batch->units_count;
        $usedQuantity = BatchSellOrder::where('batch_id', $batch->id)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where('id', '!=', $id)
            ->sum('quantity');
        
        // إضافة الكمية الحالية لهذا السجل لأننا سنستبدلها
        $currentQuantity = $sellOrder->quantity;
        $availableBatchQuantity = ($batchTotalQuantity - $usedQuantity) + $currentQuantity;
        
        $requestedQuantity = $request->quantity;
        
        if ($requestedQuantity > $availableBatchQuantity) {
            $notify[] = ['error', 'The requested quantity exceeds the available batch quantity. Available: ' . showAmount($availableBatchQuantity, 4, currencyFormat: false) . ' ' . ($batch->product->unit->symbol ?? 'Unit')];
            return back()->withNotify($notify)->withInput();
        }

        $oldProductId = $sellOrder->batch ? $sellOrder->batch->product_id : null;
        
        // حفظ الكمية الأصلية والمتبقية قبل التعديل
        $oldQuantity = $sellOrder->quantity;
        $oldAvailableQuantity = $sellOrder->available_quantity ?? $sellOrder->quantity;
        
        // حساب الكمية المباعة (التي لا يمكن تغييرها)
        $soldQuantity = $oldQuantity - $oldAvailableQuantity;
        
        $sellOrder->batch_id = $request->batch_id;
        $sellOrder->product_id = $batch->product_id;
        $sellOrder->warehouse_id = $batch->warehouse_id;
        $sellOrder->unit_id = $batch->product->unit_id; // من المنتج
        $sellOrder->currency_id = $batch->product->currency_id; // من المنتج
        
        // تحديث الكمية الأصلية
        $sellOrder->quantity = $request->quantity;
        
        // حساب الكمية المتبقية الجديدة = الكمية الأصلية الجديدة - الكمية المباعة
        // لكن لا يمكن أن تكون المتبقية أكبر من الأصلية
        $newAvailableQuantity = $request->quantity - $soldQuantity;
        $sellOrder->available_quantity = max(0, min($newAvailableQuantity, $request->quantity));
        $sellOrder->sell_price = $request->sell_price;
        // sell_order_code لا يتم تعديله
        $sellOrder->status = $request->status == 1 ? Status::SELL_ORDER_ACTIVE : Status::SELL_ORDER_INACTIVE;
        $sellOrder->save();

        // تحديث سعر السوق للمنتج القديم والجديد
        if ($oldProductId) {
            Batch::updateMarketPrice($oldProductId);
        }
        Batch::updateMarketPrice($batch->product_id);

        $notify[] = ['success', 'Batch sell order updated successfully'];
        return redirect()->route('admin.batch-sell-order.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $sellOrder = BatchSellOrder::findOrFail($id);
        $productId = $sellOrder->batch ? $sellOrder->batch->product_id : null;
        $sellOrder->delete();

        // تحديث سعر السوق للمنتج
        if ($productId) {
            Batch::updateMarketPrice($productId);
        }

        $notify[] = ['success', 'Batch sell order deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $sellOrder = BatchSellOrder::findOrFail($id);
        $productId = $sellOrder->batch ? $sellOrder->batch->product_id : null;
        
        if ($sellOrder->status == Status::SELL_ORDER_ACTIVE) {
            $sellOrder->status = Status::SELL_ORDER_INACTIVE;
        } elseif ($sellOrder->status == Status::SELL_ORDER_INACTIVE) {
            $sellOrder->status = Status::SELL_ORDER_ACTIVE;
        }
        // لا يمكن تغيير حالة SOLD أو CANCELLED
        $sellOrder->save();

        // تحديث سعر السوق للمنتج
        if ($productId) {
            Batch::updateMarketPrice($productId);
        }

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }
}
