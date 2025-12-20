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
            $table->decimal('available_units_count', 10, 2)->nullable()->after('units_count');
        });

        // تعيين القيمة الافتراضية: available_units_count = units_count
        \DB::statement('UPDATE batches SET available_units_count = units_count WHERE available_units_count IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('available_units_count');
        });
    }
};
