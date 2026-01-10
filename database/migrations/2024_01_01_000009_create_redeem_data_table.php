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
        Schema::create('redeem_data', function (Blueprint $table) {
            $table->id();
            $table->integer('bean_history_id')->default(0);
            $table->text('order_details')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_type')->default('pickup')->comment('pickup, shipping');
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->decimal('shipping_cost', 28, 8)->default(0);
            $table->decimal('distance', 10, 2)->nullable()->comment('Distance in km');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_data');
    }
};

