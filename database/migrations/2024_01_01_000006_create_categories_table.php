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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->integer('karat')->default(0);
            $table->decimal('price', 28, 8)->default(0);
            $table->decimal('change_1h', 5, 2)->default(0);
            $table->decimal('change_24h', 5, 2)->default(0);
            $table->decimal('change_7d', 5, 2)->default(0);
            $table->decimal('change_30d', 5, 2)->default(0);
            $table->decimal('change_90d', 5, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

