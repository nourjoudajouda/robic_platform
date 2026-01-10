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
            if (!Schema::hasColumn('batches', 'quality_grade')) {
                $table->string('quality_grade')->nullable()->after('batch_code');
            }
            if (!Schema::hasColumn('batches', 'origin_country')) {
                $table->string('origin_country')->nullable()->after('quality_grade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['quality_grade', 'origin_country']);
        });
    }
};
