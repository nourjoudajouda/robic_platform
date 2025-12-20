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
        Schema::table('bean_history', function (Blueprint $table) {
            $table->foreignId('item_unit_id')->nullable()->after('quantity')->constrained('units')->onDelete('set null');
            $table->foreignId('currency_id')->nullable()->after('amount')->constrained('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bean_history', function (Blueprint $table) {
            $table->dropForeign(['item_unit_id']);
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['item_unit_id', 'currency_id']);
        });
    }
};
