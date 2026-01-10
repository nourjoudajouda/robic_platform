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
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->string('batch_code');
            $table->string('type')->default('admin_created')->comment('admin_created or user_sale');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('parent_ids')->nullable()->comment('Array of original batch IDs if from user sale');
            $table->string('quality_grade')->nullable();
            $table->string('origin_country')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->date('exp_date')->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('buy_price', 10, 2)->nullable();
            $table->string('attachment')->nullable();
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
