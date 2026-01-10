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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('method_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('currency', 40)->nullable();
            $table->decimal('rate', 28, 8)->default(0);
            $table->decimal('charge', 28, 8)->default(0);
            $table->string('trx', 40)->nullable();
            $table->decimal('final_amount', 28, 8)->default(0);
            $table->decimal('after_charge', 28, 8)->default(0);
            $table->text('withdraw_information')->nullable();
            $table->tinyInteger('status')->default(0)->comment('1=>success, 2=>pending, 3=>cancel');
            $table->text('admin_feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

