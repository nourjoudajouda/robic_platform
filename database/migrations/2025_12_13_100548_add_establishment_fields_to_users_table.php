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
            $table->enum('user_type', ['individual', 'establishment'])->default('individual')->after('lastname');
            $table->string('establishment_name')->nullable()->after('user_type');
            $table->string('commercial_registration')->nullable()->after('establishment_name');
            $table->text('establishment_info')->nullable()->after('commercial_registration');
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
