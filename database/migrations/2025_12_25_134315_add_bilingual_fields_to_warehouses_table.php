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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->string('location_en')->nullable()->after('location');
            $table->string('location_ar')->nullable()->after('location_en');
            $table->text('address_en')->nullable()->after('address');
            $table->text('address_ar')->nullable()->after('address_en');
            $table->string('manager_name_en')->nullable()->after('manager_name');
            $table->string('manager_name_ar')->nullable()->after('manager_name_en');
        });
        
        // Migrate existing data
        \DB::statement("UPDATE warehouses SET name_en = name, name_ar = name WHERE name_en IS NULL");
        \DB::statement("UPDATE warehouses SET location_en = location, location_ar = location WHERE location_en IS NULL");
        \DB::statement("UPDATE warehouses SET address_en = address, address_ar = address WHERE address_en IS NULL AND address IS NOT NULL");
        \DB::statement("UPDATE warehouses SET manager_name_en = manager_name, manager_name_ar = manager_name WHERE manager_name_en IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'location_en', 'location_ar', 'address_en', 'address_ar', 'manager_name_en', 'manager_name_ar']);
        });
    }
};
