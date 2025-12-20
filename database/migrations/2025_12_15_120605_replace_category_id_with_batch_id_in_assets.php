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
        Schema::table('assets', function (Blueprint $table) {
            // التحقق من وجود foreign key قبل حذفه
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'assets' 
                AND COLUMN_NAME = 'category_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign([$foreignKey->CONSTRAINT_NAME]);
            }
            
            // إزالة category_id
            if (Schema::hasColumn('assets', 'category_id')) {
                $table->dropColumn('category_id');
            }
            
            // إضافة batch_id
            if (!Schema::hasColumn('assets', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->after('user_id')->constrained('batches')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // إزالة batch_id
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
            
            // إعادة category_id
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained('categories')->onDelete('set null');
        });
    }
};
