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
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('general_settings', 'chart_price_from')) {
                    $table->decimal('chart_price_from', 10, 2)->default(0)->after('redeem_option');
                }
                if (!Schema::hasColumn('general_settings', 'chart_price_to')) {
                    $table->decimal('chart_price_to', 10, 2)->default(20)->after('chart_price_from');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn(['chart_price_from', 'chart_price_to']);
            });
        }
    }
};
