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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['individual', 'establishment'])->default('individual')->after('lastname');
            }
            if (!Schema::hasColumn('users', 'establishment_name')) {
                $table->string('establishment_name')->nullable()->after('user_type');
            }
            if (!Schema::hasColumn('users', 'commercial_registration')) {
                $table->string('commercial_registration')->nullable()->after('establishment_name');
            }
            if (!Schema::hasColumn('users', 'establishment_info')) {
                $table->text('establishment_info')->nullable()->after('commercial_registration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'establishment_name', 'commercial_registration', 'establishment_info']);
        });
    }
};
