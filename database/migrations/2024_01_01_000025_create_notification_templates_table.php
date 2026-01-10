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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('act', 40)->nullable();
            $table->string('name', 40)->nullable();
            $table->string('subject')->nullable();
            $table->string('push_title')->nullable();
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->text('push_body')->nullable();
            $table->text('shortcodes')->nullable();
            $table->tinyInteger('email_status')->default(1);
            $table->string('email_sent_from_name', 40)->nullable();
            $table->string('email_sent_from_address', 40)->nullable();
            $table->tinyInteger('sms_status')->default(1);
            $table->string('sms_sent_from', 40)->nullable();
            $table->tinyInteger('push_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

