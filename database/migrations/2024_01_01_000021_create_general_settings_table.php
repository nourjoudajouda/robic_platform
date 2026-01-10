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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 40)->nullable();
            $table->string('cur_text', 40)->nullable()->comment('currency text');
            $table->string('cur_sym', 40)->nullable()->comment('currency symbol');
            $table->string('email_from', 40)->nullable();
            $table->string('email_from_name')->nullable();
            $table->text('email_template')->nullable();
            $table->string('sms_template')->nullable();
            $table->string('sms_from')->nullable();
            $table->string('push_title')->nullable();
            $table->string('push_template')->nullable();
            $table->string('base_color', 40)->nullable();
            $table->string('secondary_color', 40)->nullable();
            $table->text('mail_config')->nullable()->comment('email configuration');
            $table->text('sms_config')->nullable();
            $table->text('firebase_config')->nullable();
            $table->text('global_shortcodes')->nullable();
            $table->tinyInteger('kv')->default(0);
            $table->tinyInteger('ev')->default(0)->comment('email verification, 0 - dont check, 1 - check');
            $table->tinyInteger('en')->default(0)->comment('email notification, 0 - dont send, 1 - send');
            $table->tinyInteger('sv')->default(0)->comment('mobile verication, 0 - dont check, 1 - check');
            $table->tinyInteger('sn')->default(0)->comment('sms notification, 0 - dont send, 1 - send');
            $table->tinyInteger('pn')->default(1);
            $table->tinyInteger('force_ssl')->default(0);
            $table->tinyInteger('redeem_option')->default(1);
            $table->decimal('pickup_fee', 10, 2)->default(0);
            $table->decimal('chart_price_from', 10, 2)->default(0);
            $table->decimal('chart_price_to', 10, 2)->default(20);
            $table->tinyInteger('maintenance_mode')->default(0);
            $table->tinyInteger('secure_password')->default(0);
            $table->tinyInteger('agree')->default(0);
            $table->tinyInteger('multi_language')->default(1);
            $table->tinyInteger('registration')->default(0)->comment('0: Off, 1: On');
            $table->string('active_template', 40)->nullable();
            $table->text('socialite_credentials')->nullable();
            $table->dateTime('last_cron')->nullable();
            $table->string('available_version', 40)->nullable();
            $table->tinyInteger('system_customized')->default(0);
            $table->integer('paginate_number')->default(0);
            $table->tinyInteger('currency_format')->default(0)->comment('1=>Both, 2=>Text Only, 3=>Symbol Only');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};

