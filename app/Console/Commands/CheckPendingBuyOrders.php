<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\PendingBuyOrder;
use Illuminate\Console\Command;

class CheckPendingBuyOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pending:check-buy-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pending buy orders and notify users when quantity becomes available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking pending buy orders...');
        
        // جلب جميع الطلبات المعلقة
        $pendingOrders = PendingBuyOrder::where('status', Status::PENDING_BUY_ORDER)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->with('product', 'user')
            ->get();
        
        $notifiedCount = 0;
        
        foreach ($pendingOrders as $pendingOrder) {
            // التحقق من توفر الكمية بالسعر المطلوب
            $availableQuantity = $this->getAvailableQuantityAtPrice(
                $pendingOrder->product_id, 
                $pendingOrder->requested_price
            );
            
            if ($availableQuantity >= $pendingOrder->pending_quantity) {
                // إرسال إشعار للمستخدم
                $this->notifyUser($pendingOrder, $availableQuantity);
                $notifiedCount++;
            }
        }
        
        $this->info("Checked {$pendingOrders->count()} pending orders. Notified {$notifiedCount} users.");
        
        return 0;
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
    
    /**
     * إرسال إشعار للمستخدم
     */
    private function notifyUser($pendingOrder, $availableQuantity)
    {
        // تحديث تاريخ آخر إشعار
        $pendingOrder->notified_at = now();
        $pendingOrder->save();
        
        // إرسال الإشعار
        notify($pendingOrder->user, 'PENDING_ORDER_AVAILABLE', [
            'product' => $pendingOrder->product->name ?? 'Green Coffee',
            'quantity' => showAmount($pendingOrder->pending_quantity, 4, currencyFormat: false),
            'price' => showAmount($pendingOrder->requested_price, 2),
            'available_quantity' => showAmount($availableQuantity, 4, currencyFormat: false),
            'order_code' => $pendingOrder->order_code,
        ]);
    }
}
