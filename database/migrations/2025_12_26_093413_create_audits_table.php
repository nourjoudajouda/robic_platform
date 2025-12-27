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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('User ID if action is from user panel');
            $table->unsignedBigInteger('admin_id')->nullable()->comment('Admin ID if action is from admin panel');
            $table->enum('user_type', ['user', 'admin'])->comment('Type of user who performed the action');
            $table->string('action')->comment('Action performed (e.g., create, update, delete, login)');
            $table->text('description')->nullable()->comment('Description of the action');
            $table->string('model_type')->nullable()->comment('Model class name (e.g., User, Product)');
            $table->unsignedBigInteger('model_id')->nullable()->comment('Model ID');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent/browser information');
            $table->string('route')->nullable()->comment('Route/URL where action occurred');
            $table->string('method', 10)->nullable()->comment('HTTP method (GET, POST, PUT, DELETE)');
            $table->json('request_data')->nullable()->comment('Request data (optional)');
            $table->json('old_values')->nullable()->comment('Old values before update (for update actions)');
            $table->json('new_values')->nullable()->comment('New values after update (for update actions)');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('user_id');
            $table->index('admin_id');
            $table->index('user_type');
            $table->index('action');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
