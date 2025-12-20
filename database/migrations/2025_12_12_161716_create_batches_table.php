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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('units_count', 10, 2);
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('items_count_per_unit', 10, 2);
            $table->foreignId('item_unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->string('batch_code')->unique();
            $table->date('exp_date')->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('buy_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
