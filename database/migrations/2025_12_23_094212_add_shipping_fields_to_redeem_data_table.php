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
        if (Schema::hasTable('redeem_data')) {
            Schema::table('redeem_data', function (Blueprint $table) {
                if (!Schema::hasColumn('redeem_data', 'delivery_type')) {
                    $table->string('delivery_type')->default('pickup')->after('delivery_address')->comment('pickup, shipping');
                }
                if (!Schema::hasColumn('redeem_data', 'shipping_method_id')) {
                    $table->unsignedBigInteger('shipping_method_id')->nullable()->after('delivery_type');
                }
                if (!Schema::hasColumn('redeem_data', 'shipping_cost')) {
                    $table->decimal('shipping_cost', 28, 8)->default(0)->after('shipping_method_id');
                }
                if (!Schema::hasColumn('redeem_data', 'distance')) {
                    $table->decimal('distance', 10, 2)->nullable()->after('shipping_cost')->comment('Distance in km');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('redeem_data')) {
            Schema::table('redeem_data', function (Blueprint $table) {
                if (Schema::hasColumn('redeem_data', 'shipping_method_id')) {
                    $table->dropForeign(['shipping_method_id']);
                }
                $table->dropColumn(['delivery_type', 'shipping_method_id', 'shipping_cost', 'distance']);
            });
        }
    }
};
