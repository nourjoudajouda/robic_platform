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
        Schema::create('batch_sell_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            
            // معلومات من batch
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('item_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            
            // معلومات البيع
            $table->decimal('quantity', 10, 4)->comment('الكمية الإجمالية بالوحدات (item_unit)');
            $table->decimal('available_quantity', 10, 4)->nullable()->comment('الكمية المعروضة للبيع');
            $table->decimal('sell_price', 10, 2)->comment('سعر البيع');
            $table->string('sell_order_code')->unique()->comment('كود طلب البيع');
            
            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive, 2=sold, 3=cancelled');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['batch_id', 'status']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_sell_orders');
    }
};
