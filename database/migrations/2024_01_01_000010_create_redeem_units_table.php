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
        Schema::create('redeem_units', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 28, 8)->default(0);
            $table->tinyInteger('type')->default(0)->comment('1: bar, 2: coin');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_units');
    }
};

