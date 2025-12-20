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
        Schema::table('batches', function (Blueprint $table) {
            // حذف حقل price (لأن sell_price موجود بالفعل)
            $table->dropColumn('price');
            // حذف حقل buy_price
            $table->dropColumn('buy_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // إرجاع التغييرات
            $table->decimal('price', 10, 2);
            $table->decimal('buy_price', 10, 2)->nullable();
        });
    }
};
