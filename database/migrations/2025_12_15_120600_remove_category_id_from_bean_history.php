<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bean_history', function (Blueprint $table) {
            // التحقق من وجود العمود قبل حذفه
            if (Schema::hasColumn('bean_history', 'category_id')) {
                // التحقق من وجود foreign key قبل حذفه
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'bean_history' 
                    AND COLUMN_NAME = 'category_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                foreach ($foreignKeys as $foreignKey) {
                    try {
                        $table->dropForeign([$foreignKey->CONSTRAINT_NAME]);
                    } catch (\Exception $e) {
                        // Foreign key might not exist
                    }
                }
                
                $table->dropColumn('category_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bean_history', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('asset_id')->constrained('categories')->onDelete('set null');
        });
    }
};
