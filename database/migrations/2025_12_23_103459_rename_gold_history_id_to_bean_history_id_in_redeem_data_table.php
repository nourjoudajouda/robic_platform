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
        if (Schema::hasTable('redeem_data') && Schema::hasColumn('redeem_data', 'gold_history_id')) {
            Schema::table('redeem_data', function (Blueprint $table) {
                $table->renameColumn('gold_history_id', 'bean_history_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redeem_data', function (Blueprint $table) {
            $table->renameColumn('bean_history_id', 'gold_history_id');
        });
    }
};
