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
        Schema::create('price_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->text('configuration')->nullable();
            $table->text('instruction')->nullable();
            $table->string('image', 40)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_apis');
    }
};

