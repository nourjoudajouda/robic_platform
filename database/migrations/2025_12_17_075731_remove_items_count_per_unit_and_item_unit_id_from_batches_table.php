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
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['item_unit_id']);
            $table->dropColumn(['items_count_per_unit', 'item_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('items_count_per_unit', 10, 2)->after('unit_id');
            $table->foreignId('item_unit_id')->after('items_count_per_unit')->constrained('units')->onDelete('cascade');
        });
    }
};
