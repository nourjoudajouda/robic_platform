<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_sell_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // معلومات من asset
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
            $table->decimal('buy_price', 10, 2)->nullable()->comment('سعر الشراء الأصلي');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('item_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            
            // معلومات البيع
            $table->decimal('quantity', 10, 4)->comment('الكمية الإجمالية');
            $table->decimal('available_quantity', 10, 4)->nullable()->comment('الكمية المعروضة للبيع');
            $table->decimal('sell_price', 10, 2)->comment('سعر البيع');
            $table->string('sell_order_code')->unique()->comment('كود طلب البيع');
            
            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive, 2=sold, 3=cancelled');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['asset_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sell_orders');
    }
};
