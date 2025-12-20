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
        Schema::create('pending_buy_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // معلومات الطلب
            $table->decimal('requested_quantity', 10, 4)->comment('الكمية المطلوبة');
            $table->decimal('requested_price', 10, 2)->comment('السعر المطلوب');
            $table->decimal('fulfilled_quantity', 10, 4)->default(0)->comment('الكمية التي تم شراؤها');
            $table->decimal('pending_quantity', 10, 4)->comment('الكمية المعلقة');
            
            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->string('order_code')->unique()->comment('كود الطلب المعلق');
            
            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=fulfilled, 3=cancelled, 4=expired');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الطلب');
            $table->timestamp('notified_at')->nullable()->comment('تاريخ آخر إشعار');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'requested_price', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_buy_orders');
    }
};

            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=fulfilled, 3=cancelled, 4=expired');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الطلب');
            $table->timestamp('notified_at')->nullable()->comment('تاريخ آخر إشعار');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'requested_price', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_buy_orders');
    }
};

            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=fulfilled, 3=cancelled, 4=expired');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الطلب');
            $table->timestamp('notified_at')->nullable()->comment('تاريخ آخر إشعار');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'requested_price', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_buy_orders');
    }
};

            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=fulfilled, 3=cancelled, 4=expired');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الطلب');
            $table->timestamp('notified_at')->nullable()->comment('تاريخ آخر إشعار');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'requested_price', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_buy_orders');
    }
};

            // الحالة
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=fulfilled, 3=cancelled, 4=expired');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الطلب');
            $table->timestamp('notified_at')->nullable()->comment('تاريخ آخر إشعار');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'requested_price', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_buy_orders');
    }
};
