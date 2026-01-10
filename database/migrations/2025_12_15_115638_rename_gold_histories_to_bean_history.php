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
        // جدول bean_history تم إنشاؤه مباشرة في migration سابق
        // لا حاجة لإعادة التسمية
        if (Schema::hasTable('gold_histories') && !Schema::hasTable('bean_history')) {
            Schema::rename('gold_histories', 'bean_history');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('bean_history', 'gold_histories');
    }
};
