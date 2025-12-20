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
        // هذا migration تم استبداله - batch_id موجود بالفعل في bean_history
        // لا حاجة لتنفيذ أي شيء هنا
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا حاجة لتنفيذ أي شيء هنا
    }
};
