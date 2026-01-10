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
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->string('alias', 40)->nullable();
            $table->text('action')->nullable();
            $table->string('url')->nullable();
            $table->integer('cron_schedule_id')->default(0);
            $table->dateTime('next_run')->nullable();
            $table->dateTime('last_run')->nullable();
            $table->tinyInteger('is_running')->default(1);
            $table->tinyInteger('is_default')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_jobs');
    }
};

