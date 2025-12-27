<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\PendingBuyOrder;
use Illuminate\Http\Request;

class PendingBuyOrderController extends Controller
{
    /**
     * عرض جميع الطلبات المعلقة
     */
    public function index()
    {
        $pageTitle = 'Pending Buy Orders';
        
        $pendingOrders = PendingBuyOrder::with('user', 'product.unit', 'product.currency')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());
        
        return view('admin.pending_buy_orders.index', compact('pageTitle', 'pendingOrders'));
    }
    
    /**
     * عرض تفاصيل طلب معلق
     */
    public function show($id)
    {
        $pageTitle = 'Pending Order Details';
        
        $pendingOrder = PendingBuyOrder::with('user', 'product.unit', 'product.currency')
            ->findOrFail($id);
        
        // التحقق من توفر الكمية بالسعر المطلوب
        $availableQuantity = $this->getAvailableQuantityAtPrice(
            $pendingOrder->product_id,
            $pendingOrder->requested_price
        );
        
        return view('admin.pending_buy_orders.show', compact('pageTitle', 'pendingOrder', 'availableQuantity'));
    }
    
    /**
     * الموافقة على طلب معلق (تنفيذه)
     */
    public function approve($id)
    {
        $pendingOrder = PendingBuyOrder::findOrFail($id);
        
        if ($pendingOrder->status != Status::PENDING_BUY_ORDER) {
            $notify[] = ['error', 'This order is not pending'];
            return back()->withNotify($notify);
        }
        
        // التحقق من توفر الكمية
        $availableQuantity = $this->getAvailableQuantityAtPrice(
            $pendingOrder->product_id,
            $pendingOrder->requested_price
        );
        
        if ($availableQuantity < $pendingOrder->pending_quantity) {
            $notify[] = ['error', 'Insufficient quantity available. Available: ' . showAmount($availableQuantity, 4, currencyFormat: false)];
            return back()->withNotify($notify);
        }
        
        $pendingOrder->status = Status::PENDING_BUY_FULFILLED;
        $pendingOrder->save();
        
        // إرسال إشعار للمستخدم
        notify($pendingOrder->user, 'PENDING_ORDER_APPROVED', [
            'product' => $pendingOrder->product->name ?? 'Green Coffee',
            'quantity' => showAmount($pendingOrder->pending_quantity, 4, currencyFormat: false),
            'price' => showAmount($pendingOrder->requested_price, 2),
            'order_code' => $pendingOrder->order_code,
        ]);
        
        $notify[] = ['success', 'Pending order approved successfully. User has been notified.'];
        return back()->withNotify($notify);
    }
    
    /**
     * رفض طلب معلق
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);
        
        $pendingOrder = PendingBuyOrder::findOrFail($id);
        
        if ($pendingOrder->status != Status::PENDING_BUY_ORDER) {
            $notify[] = ['error', 'This order is not pending'];
            return back()->withNotify($notify);
        }
        
        $pendingOrder->status = Status::PENDING_BUY_CANCELLED;
        if ($request->notes) {
            $pendingOrder->notes = $request->notes;
        }
        $pendingOrder->save();
        
        // إرسال إشعار للمستخدم
        notify($pendingOrder->user, 'PENDING_ORDER_REJECTED', [
            'product' => $pendingOrder->product->name ?? 'Green Coffee',
            'quantity' => showAmount($pendingOrder->pending_quantity, 4, currencyFormat: false),
            'price' => showAmount($pendingOrder->requested_price, 2),
            'order_code' => $pendingOrder->order_code,
            'notes' => $request->notes ?? 'No reason provided',
        ]);
        
        $notify[] = ['success', 'Pending order rejected successfully. User has been notified.'];
        return back()->withNotify($notify);
    }
    
    /**
     * تعليم طلب كمنتهي
     */
    public function markAsExpired($id)
    {
        $pendingOrder = PendingBuyOrder::findOrFail($id);
        
        if ($pendingOrder->status != Status::PENDING_BUY_ORDER) {
            $notify[] = ['error', 'This order is not pending'];
            return back()->withNotify($notify);
        }
        
        $pendingOrder->status = Status::PENDING_BUY_EXPIRED;
        $pendingOrder->save();
        
        $notify[] = ['success', 'Pending order marked as expired'];
        return back()->withNotify($notify);
    }
    
    /**
     * حذف طلب معلق
     */
    public function destroy($id)
    {
        $pendingOrder = PendingBuyOrder::findOrFail($id);
        $pendingOrder->delete();
        
        $notify[] = ['success', 'Pending order deleted successfully'];
        return back()->withNotify($notify);
    }
    
    /**
     * الحصول على الكمية المتوفرة بسعر معين
     */
    private function getAvailableQuantityAtPrice($productId, $price)
    {
        $batchOrders = \App\Models\BatchSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where('sell_price', $price)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        $userOrders = \App\Models\UserSellOrder::where('product_id', $productId)
            ->where('status', Status::SELL_ORDER_ACTIVE)
            ->where('sell_price', $price)
            ->where(function($q) {
                $q->whereRaw('(available_quantity IS NOT NULL AND available_quantity > 0)')
                  ->orWhereRaw('(available_quantity IS NULL AND quantity > 0)');
            })
            ->get()
            ->sum(function($order) {
                return $order->available_quantity ?? $order->quantity;
            });
        
        return $batchOrders + $userOrders;
    }
}
