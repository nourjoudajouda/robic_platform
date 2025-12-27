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
        Schema::table('charge_limits', function (Blueprint $table) {
            $table->decimal('pickup_fee', 10, 2)->default(0)->nullable()->after('vat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charge_limits', function (Blueprint $table) {
            $table->dropColumn('pickup_fee');
        });
    }
};
