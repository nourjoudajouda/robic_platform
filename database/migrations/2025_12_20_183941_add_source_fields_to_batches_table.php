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
        Schema::table('batches', function (Blueprint $table) {
            // نوع الـ batch: admin_created (أضافها الأدمن) أو user_sale (اشتريت من المستخدم)
            $table->string('type')->default('admin_created')->after('batch_code')->comment('admin_created or user_sale');
            
            // معرف المستخدم إذا كان الـ batch جاي من بيع مستخدم
            $table->foreignId('user_id')->nullable()->after('type')->constrained('users')->onDelete('set null')->comment('User ID if batch is from user sale');
            
            // معرفات الـ batches الأصلية التي جاءت منها هذه الكميات (JSON array)
            $table->json('parent_ids')->nullable()->after('user_id')->comment('Array of original batch IDs if from user sale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['type', 'user_id', 'parent_ids']);
        });
    }
};
