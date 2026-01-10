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
        if (Schema::hasTable('charge_limits')) {
            Schema::table('charge_limits', function (Blueprint $table) {
                if (!Schema::hasColumn('charge_limits', 'pickup_fee')) {
                    if (Schema::hasColumn('charge_limits', 'vat')) {
                        $table->decimal('pickup_fee', 10, 2)->default(0)->nullable()->after('vat');
                    } else {
                        $table->decimal('pickup_fee', 10, 2)->default(0)->nullable();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('charge_limits')) {
            Schema::table('charge_limits', function (Blueprint $table) {
                $table->dropColumn('pickup_fee');
            });
        }
    }
};
