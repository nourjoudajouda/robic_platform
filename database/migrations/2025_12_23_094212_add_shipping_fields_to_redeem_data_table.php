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
        Schema::table('redeem_data', function (Blueprint $table) {
            $table->string('delivery_type')->default('pickup')->after('delivery_address')->comment('pickup, shipping');
            $table->foreignId('shipping_method_id')->nullable()->after('delivery_type')->constrained('shipping_methods')->onDelete('set null');
            $table->decimal('shipping_cost', 28, 8)->default(0)->after('shipping_method_id');
            $table->decimal('distance', 10, 2)->nullable()->after('shipping_cost')->comment('Distance in km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redeem_data', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn(['delivery_type', 'shipping_method_id', 'shipping_cost', 'distance']);
        });
    }
};
