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
        Schema::table('assets', function (Blueprint $table) {
            // إضافة product_id و warehouse_id من batch
            $table->foreignId('product_id')->nullable()->after('batch_id')->constrained('products')->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('warehouses')->onDelete('set null');
            
            // إضافة سعر الشراء (buy_price)
            $table->decimal('buy_price', 10, 2)->nullable()->after('warehouse_id');
            
            // إضافة الوحدات
            $table->foreignId('unit_id')->nullable()->after('buy_price')->constrained('units')->onDelete('set null');
            $table->foreignId('item_unit_id')->nullable()->after('unit_id')->constrained('units')->onDelete('set null');
            
            // إضافة العملة
            $table->foreignId('currency_id')->nullable()->after('item_unit_id')->constrained('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['item_unit_id']);
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['product_id', 'warehouse_id', 'buy_price', 'unit_id', 'item_unit_id', 'currency_id']);
        });
    }
};
