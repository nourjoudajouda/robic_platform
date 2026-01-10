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
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('general_settings', 'bank_transfer')) {
                    $table->text('bank_transfer')->nullable()->after('socialite_credentials');
                }
                if (!Schema::hasColumn('general_settings', 'deposit_instructions')) {
                    $table->text('deposit_instructions')->nullable()->after('bank_transfer');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (Schema::hasColumn('general_settings', 'bank_transfer')) {
                    $table->dropColumn('bank_transfer');
                }
                if (Schema::hasColumn('general_settings', 'deposit_instructions')) {
                    $table->dropColumn('deposit_instructions');
                }
            });
        }
    }
};
