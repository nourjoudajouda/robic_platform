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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->default(0);
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('post_balance', 28, 8)->default(0);
            $table->decimal('charge', 28, 8)->default(0);
            $table->string('trx_type', 20)->nullable();
            $table->string('trx', 40)->nullable();
            $table->text('details')->nullable();
            $table->string('remark', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

