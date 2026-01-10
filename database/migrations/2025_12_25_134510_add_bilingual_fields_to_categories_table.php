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
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (!Schema::hasColumn('categories', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name_en');
                }
            });
            
            // Migrate existing data
            if (Schema::hasColumn('categories', 'name_en')) {
                \DB::statement("UPDATE categories SET name_en = name, name_ar = name WHERE name_en IS NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn(['name_en', 'name_ar']);
            });
        }
    }
};
