<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncatePurchaseTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase:truncate {--force : Force truncate without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all tables related to purchase process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to truncate all purchase-related tables? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting truncation of purchase-related tables...');

        try {
            // تعطيل فحص Foreign Keys مؤقتاً
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // الجداول التي تعتمد على غيرها (يجب حذفها أولاً)
            $dependentTables = [
                'bean_history',
                'user_sell_orders',
                'batch_sell_orders',
                'assets',
            ];

            foreach ($dependentTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✓ Truncated: {$table}");
                }
            }

            // الجداول الأساسية
            $mainTables = [
                'batches',
                'market_price_history',
            ];

            foreach ($mainTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✓ Truncated: {$table}");
                }
            }

            // المنتجات والمستودعات (يمكن الاحتفاظ بها أو حذفها حسب الحاجة)
            $optionalTables = [
                'products',
                'warehouses',
            ];

            foreach ($optionalTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✓ Truncated: {$table}");
                }
            }

            // إعادة تفعيل فحص Foreign Keys
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('');
            $this->info('✓ All purchase-related tables have been truncated successfully!');
            $this->warn('Note: Products and Warehouses have also been truncated. You may need to recreate them.');

            return 0;
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
