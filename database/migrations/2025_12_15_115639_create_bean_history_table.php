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
        Schema::create('bean_history', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->integer('asset_id')->default(0);
            $table->integer('recipient_id')->default(0);
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('quantity', 28, 8)->default(0);
            $table->unsignedBigInteger('item_unit_id')->nullable();
            $table->decimal('amount', 28, 8)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('charge', 28, 8)->default(0);
            $table->decimal('vat', 28, 8)->default(0);
            $table->tinyInteger('type')->default(0);
            $table->string('trx', 40)->nullable();
            $table->timestamps();
            
            // Foreign keys will be added in separate migrations after tables are created
            // Note: Foreign keys are added later in migration 2025_12_16_102451_add_item_unit_id_and_currency_id_to_bean_history_table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bean_history');
    }
};

