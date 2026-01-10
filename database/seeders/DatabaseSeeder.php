<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Asset;
use App\Models\BatchSellOrder;
use App\Models\UserSellOrder;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\BeanHistory;
use App\Models\Wallet;
use App\Models\MarketPriceHistory;
use App\Models\Role;
use App\Constants\Status;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Clear existing data (optional - comment out if you want to keep existing data)
        // $this->command->warn('⚠️  Clearing existing data...');
        // $this->clearData();

        // Seed Roles and Permissions first
        $this->command->info('📋 Seeding roles and permissions...');
        $this->call(RolesAndPermissionsSeeder::class);

        // Seed Currencies
        $this->command->info('💱 Seeding currencies...');
        $currencies = $this->seedCurrencies();

        // Seed Units
        $this->command->info('📦 Seeding units...');
        $units = $this->seedUnits();

        // Seed Warehouses
        $this->command->info('🏭 Seeding warehouses...');
        $warehouses = $this->seedWarehouses();

        // Seed Products
        $this->command->info('☕ Seeding products...');
        $products = $this->seedProducts($units, $currencies);

        // Seed Admins
        $this->command->info('👨‍💼 Seeding admins...');
        $admins = $this->seedAdmins();

        // Seed Users
        $this->command->info('👥 Seeding users...');
        $users = $this->seedUsers();

        // Seed Wallets for users
        $this->command->info('💰 Seeding wallets...');
        $this->seedWallets($users, $currencies);

        // Seed Batches
        $this->command->info('📦 Seeding batches...');
        $batches = $this->seedBatches($products, $warehouses, $units, $currencies);

        // Seed Batch Sell Orders
        $this->command->info('🛒 Seeding batch sell orders...');
        $batchSellOrders = $this->seedBatchSellOrders($batches, $products, $warehouses, $units, $currencies);

        // Seed Assets
        $this->command->info('💎 Seeding assets...');
        $assets = $this->seedAssets($users, $batches, $products, $warehouses, $units, $currencies);

        // Seed User Sell Orders
        $this->command->info('🛍️ Seeding user sell orders...');
        $userSellOrders = $this->seedUserSellOrders($users, $assets, $products, $warehouses, $batches, $units, $currencies);

        // Seed Transactions
        $this->command->info('💳 Seeding transactions...');
        $this->seedTransactions($users);

        // Seed Deposits
        $this->command->info('📥 Seeding deposits...');
        $this->seedDeposits($users);

        // Seed Withdrawals
        $this->command->info('📤 Seeding withdrawals...');
        $this->seedWithdrawals($users);

        // Seed Bean History (Buy/Sell)
        $this->command->info('📜 Seeding bean history...');
        $this->seedBeanHistory($users, $assets, $batches, $products, $units, $currencies);

        // Seed Market Price History
        $this->command->info('📊 Seeding market price history...');
        $this->seedMarketPriceHistory($products);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📝 Summary:');
        $this->command->info('   - Currencies: ' . count($currencies));
        $this->command->info('   - Units: ' . count($units));
        $this->command->info('   - Warehouses: ' . count($warehouses));
        $this->command->info('   - Products: ' . count($products));
        $this->command->info('   - Admins: ' . count($admins));
        $this->command->info('   - Users: ' . count($users));
        $this->command->info('   - Batches: ' . count($batches));
        $this->command->info('   - Assets: ' . count($assets));
        $this->command->info('   - Batch Sell Orders: ' . count($batchSellOrders));
        $this->command->info('   - User Sell Orders: ' . count($userSellOrders));
    }

    private function seedCurrencies()
    {
        $currencies = [
            ['code' => 'SAR', 'symbol' => 'ر.س', 'name_en' => 'Saudi Riyal', 'name_ar' => 'ريال سعودي'],
            ['code' => 'USD', 'symbol' => '$', 'name_en' => 'US Dollar', 'name_ar' => 'دولار أمريكي'],
            ['code' => 'EUR', 'symbol' => '€', 'name_en' => 'Euro', 'name_ar' => 'يورو'],
        ];

        $created = [];
        foreach ($currencies as $currency) {
            $created[] = Currency::firstOrCreate(
                ['code' => $currency['code']],
                [
                    'symbol' => $currency['symbol'],
                    'name_en' => $currency['name_en'],
                    'name_ar' => $currency['name_ar'],
                    'name' => $currency['name_en'],
                ]
            );
        }

        return $created;
    }

    private function seedUnits()
    {
        $units = [
            ['code' => 'KG', 'symbol' => 'kg', 'name_en' => 'Kilogram', 'name_ar' => 'كيلوغرام', 'description_en' => 'Weight unit', 'description_ar' => 'وحدة الوزن'],
            ['code' => 'TON', 'symbol' => 'ton', 'name_en' => 'Ton', 'name_ar' => 'طن', 'description_en' => 'Weight unit', 'description_ar' => 'وحدة الوزن'],
            ['code' => 'BAG', 'symbol' => 'bag', 'name_en' => 'Bag', 'name_ar' => 'كيس', 'description_en' => 'Packaging unit', 'description_ar' => 'وحدة التعبئة'],
        ];

        $created = [];
        foreach ($units as $unit) {
            $created[] = Unit::firstOrCreate(
                ['code' => $unit['code']],
                [
                    'symbol' => $unit['symbol'],
                    'name_en' => $unit['name_en'],
                    'name_ar' => $unit['name_ar'],
                    'name' => $unit['name_en'],
                    'description_en' => $unit['description_en'],
                    'description_ar' => $unit['description_ar'],
                    'description' => $unit['description_en'],
                ]
            );
        }

        return $created;
    }

    private function seedWarehouses()
    {
        $warehouses = [];
        for ($i = 0; $i < 5; $i++) {
            $warehouses[] = Warehouse::factory()->create();
        }
        return $warehouses;
    }

    private function seedProducts($units, $currencies)
    {
        $products = [
            ['name_en' => 'Arabica Coffee', 'name_ar' => 'قهوة أرابيكا', 'sku' => 'ARB-001'],
            ['name_en' => 'Robusta Coffee', 'name_ar' => 'قهوة روبوستا', 'sku' => 'ROB-001'],
            ['name_en' => 'Ethiopian Coffee', 'name_ar' => 'قهوة إثيوبية', 'sku' => 'ETH-001'],
            ['name_en' => 'Colombian Coffee', 'name_ar' => 'قهوة كولومبية', 'sku' => 'COL-001'],
            ['name_en' => 'Brazilian Coffee', 'name_ar' => 'قهوة برازيلية', 'sku' => 'BRA-001'],
        ];

        $created = [];
        foreach ($products as $index => $product) {
            // Generate SKU in format RO-XXX (like in ProductController)
            $prefix = 'RO';
            $sku = '';
            do {
                $number = getNumber(3);
                $sku = $prefix . '-' . $number;
            } while (Product::where('sku', $sku)->exists());
            
            $created[] = Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'name_en' => $product['name_en'],
                    'name_ar' => $product['name_ar'],
                    'name' => $product['name_en'],
                    'status' => Status::ENABLE,
                    'market_price' => rand(5000, 20000) / 100,
                    'unit_id' => $units[0]->id, // Use KG
                    'currency_id' => $currencies[0]->id, // Use SAR
                ]
            );
        }

        return $created;
    }

    private function seedAdmins()
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $warehousesTeamRole = Role::where('slug', 'warehouses_team')->first();
        $financeTeamRole = Role::where('slug', 'finance_team')->first();

        $admins = [];

        // Super Admin
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'admin@robic.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
            ]
        );
        if ($superAdminRole && !$superAdmin->roles()->where('slug', 'super_admin')->exists()) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
        $admins[] = $superAdmin;

        // Warehouses Team Admin
        $warehouseAdmin = Admin::firstOrCreate(
            ['email' => 'warehouse@robic.com'],
            [
                'name' => 'Warehouse Manager',
                'username' => 'warehouse',
                'password' => Hash::make('password'),
            ]
        );
        if ($warehousesTeamRole && !$warehouseAdmin->roles()->where('slug', 'warehouses_team')->exists()) {
            $warehouseAdmin->roles()->syncWithoutDetaching([$warehousesTeamRole->id]);
        }
        $admins[] = $warehouseAdmin;

        // Finance Team Admin
        $financeAdmin = Admin::firstOrCreate(
            ['email' => 'finance@robic.com'],
            [
                'name' => 'Finance Manager',
                'username' => 'finance',
                'password' => Hash::make('password'),
            ]
        );
        if ($financeTeamRole && !$financeAdmin->roles()->where('slug', 'finance_team')->exists()) {
            $financeAdmin->roles()->syncWithoutDetaching([$financeTeamRole->id]);
        }
        $admins[] = $financeAdmin;

        // Additional random admins
        for ($i = 0; $i < 3; $i++) {
            $admin = Admin::factory()->create();
            if ($warehousesTeamRole && rand(0, 1)) {
                $admin->roles()->syncWithoutDetaching([$warehousesTeamRole->id]);
            }
            $admins[] = $admin;
        }

        return $admins;
    }

    private function seedUsers()
    {
        $users = [];

        // Create regular users
        for ($i = 0; $i < 20; $i++) {
            $users[] = User::factory()->create();
        }

        // Create establishment users
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::factory()->establishment()->create();
        }

        return $users;
    }

    private function seedWallets($users, $currencies)
    {
        foreach ($users as $user) {
            $currency = $currencies[array_rand($currencies)];
            Wallet::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'currency_id' => $currency->id,
                ],
                [
                    'balance' => $user->balance ?? rand(100000, 10000000) / 100,
                    'status' => Status::ENABLE,
                ]
            );
        }
    }

    private function seedBatches($products, $warehouses, $units, $currencies)
    {
        $batches = [];
        
        foreach ($products as $product) {
            for ($i = 0; $i < 3; $i++) {
                $warehouse = $warehouses[array_rand($warehouses)];
                
                // unit_id و currency_id يجب أن يكونا من المنتج (مثل BatchController)
                $batch = Batch::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'units_count' => rand(100, 5000), // أرقام صحيحة
                    'unit_id' => $product->unit_id, // من المنتج
                    'currency_id' => $product->currency_id, // من المنتج
                    'sell_price' => rand(5000, 20000) / 100,
                    'buy_price' => rand(4000, 18000) / 100,
                    'batch_code' => Batch::generateBatchCode(), // BT-XXX format
                    'quality_grade' => ['Premium', 'Grade A', 'Grade B', 'Standard'][array_rand(['Premium', 'Grade A', 'Grade B', 'Standard'])],
                    'origin_country' => ['Ethiopia', 'Colombia', 'Brazil', 'Yemen', 'Kenya'][array_rand(['Ethiopia', 'Colombia', 'Brazil', 'Yemen', 'Kenya'])],
                    'exp_date' => now()->addYears(rand(1, 3))->addDays(rand(0, 365)),
                    'status' => Status::ENABLE,
                    'type' => 'admin_created',
                ]);
                
                $batches[] = $batch;
            }
        }

        return $batches;
    }

    private function seedBatchSellOrders($batches, $products, $warehouses, $units, $currencies)
    {
        $sellOrders = [];

        foreach ($batches as $batch) {
            // حساب الكمية المتاحة من الباتش (أرقام صحيحة)
            $batchTotalQuantity = (int)$batch->units_count;
            
            // إنشاء 1-3 sell orders لكل batch
            $orderCount = rand(1, min(3, max(1, (int)($batchTotalQuantity / 50)))); // لا نزيد عن 3 طلبات
            
            $remainingQuantity = $batchTotalQuantity;
            
            for ($i = 0; $i < $orderCount; $i++) {
                // في آخر طلب، نأخذ كل الكمية المتبقية
                if ($i === $orderCount - 1) {
                    $quantity = $remainingQuantity;
                } else {
                    // توزيع الكمية بشكل عشوائي (لكن نضمن أن المتبقي كافٍ للطلبات المتبقية)
                    $minQuantity = max(10, (int)($remainingQuantity / ($orderCount - $i) * 0.3)); // 30% كحد أدنى
                    $maxQuantity = (int)($remainingQuantity / ($orderCount - $i) * 1.5); // 150% كحد أقصى
                    $maxQuantity = min($maxQuantity, $remainingQuantity - ($orderCount - $i - 1) * 10); // نضمن أن المتبقي كافٍ
                    $quantity = rand($minQuantity, max($minQuantity, $maxQuantity));
                }
                
                // التأكد من أن الكمية صحيحة وليست صفر
                $quantity = max(1, (int)$quantity);
                $remainingQuantity -= $quantity;
                
                // إذا لم يبق شيء، نتوقف
                if ($quantity <= 0 || $remainingQuantity < 0) {
                    break;
                }
                
                $sellPrice = $batch->sell_price + (rand(-1000, 2000) / 100);
                
                $sellOrder = BatchSellOrder::create([
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'warehouse_id' => $batch->warehouse_id,
                    'unit_id' => $batch->unit_id,
                    'currency_id' => $batch->currency_id,
                    'quantity' => $quantity, // أرقام صحيحة
                    'available_quantity' => $quantity,
                    'sell_price' => $sellPrice,
                    'sell_order_code' => BatchSellOrder::generateSellOrderCode(),
                    'status' => [Status::SELL_ORDER_ACTIVE, Status::SELL_ORDER_SOLD][array_rand([Status::SELL_ORDER_ACTIVE, Status::SELL_ORDER_SOLD])],
                ]);
                
                $sellOrders[] = $sellOrder;
                
                // إذا لم يبق شيء، نتوقف
                if ($remainingQuantity <= 0) {
                    break;
                }
            }
        }

        return $sellOrders;
    }

    private function seedAssets($users, $batches, $products, $warehouses, $units, $currencies)
    {
        $assets = [];

        // Create assets for some users from batches
        $selectedUsers = array_rand($users, min(15, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $batch = $batches[array_rand($batches)];
            $maxQuantity = min(500, $batch->units_count * 0.1);
            $quantity = rand(10000, (int)($maxQuantity * 10000)) / 10000;
            
            $asset = Asset::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'product_id' => $batch->product_id,
                    'warehouse_id' => $batch->warehouse_id,
                    'buy_price' => $batch->sell_price * 0.9,
                    'unit_id' => $batch->unit_id,
                    'item_unit_id' => $batch->item_unit_id,
                    'currency_id' => $batch->currency_id,
                    'quantity' => $quantity,
                ]
            );
            
            $assets[] = $asset;
        }

        return $assets;
    }

    private function seedUserSellOrders($users, $assets, $products, $warehouses, $batches, $units, $currencies)
    {
        $sellOrders = [];

        // Create sell orders from user assets
        $selectedAssets = array_rand($assets, min(10, count($assets)));
        if (!is_array($selectedAssets)) {
            $selectedAssets = [$selectedAssets];
        }
        
        foreach ($selectedAssets as $assetIndex) {
            $asset = $assets[$assetIndex];
            if ($asset->quantity > 0) {
                $maxQuantity = min($asset->quantity * 0.5, 500);
                $quantity = rand(5000, (int)($maxQuantity * 10000)) / 10000;
                $sellPrice = $asset->buy_price * (1 + (rand(500, 3000) / 10000));
                
                $sellOrder = UserSellOrder::create([
                    'user_id' => $asset->user_id,
                    'asset_id' => $asset->id,
                    'product_id' => $asset->product_id,
                    'warehouse_id' => $asset->warehouse_id,
                    'batch_id' => $asset->batch_id,
                    'buy_price' => $asset->buy_price,
                    'unit_id' => $asset->unit_id,
                    'item_unit_id' => $asset->item_unit_id,
                    'currency_id' => $asset->currency_id,
                    'quantity' => $quantity,
                    'available_quantity' => $quantity,
                    'sell_price' => $sellPrice,
                    'sell_order_code' => UserSellOrder::generateSellOrderCode(),
                    'status' => [Status::SELL_ORDER_ACTIVE, Status::SELL_ORDER_SOLD][array_rand([Status::SELL_ORDER_ACTIVE, Status::SELL_ORDER_SOLD])],
                ]);
                
                $sellOrders[] = $sellOrder;
            }
        }

        return $sellOrders;
    }

    private function seedTransactions($users)
    {
        $selectedUsers = array_rand($users, min(30, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $count = rand(5, 20);
            for ($i = 0; $i < $count; $i++) {
                Transaction::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    private function seedDeposits($users)
    {
        $selectedUsers = array_rand($users, min(20, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $count = rand(1, 5);
            for ($i = 0; $i < $count; $i++) {
                Deposit::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    private function seedWithdrawals($users)
    {
        $selectedUsers = array_rand($users, min(15, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                Withdrawal::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    private function seedBeanHistory($users, $assets, $batches, $products, $units, $currencies)
    {
        // Buy history
        $selectedUsers = array_rand($users, min(20, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $count = rand(3, 10);
            for ($i = 0; $i < $count; $i++) {
                $batch = $batches[array_rand($batches)];
                $quantity = rand(10000, 10000000) / 100000;
                $price = $batch->sell_price;
                $amount = $quantity * $price;
                $charge = $amount * 0.01;
                $vat = $amount * 0.15;

                BeanHistory::create([
                    'user_id' => $user->id,
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'quantity' => $quantity,
                    'item_unit_id' => $batch->item_unit_id,
                    'amount' => $amount,
                    'currency_id' => $batch->currency_id,
                    'charge' => $charge,
                    'vat' => $vat,
                    'trx' => getTrx(),
                    'type' => Status::BUY_HISTORY,
                    'created_at' => now()->subMonths(rand(0, 6))->subDays(rand(0, 30)),
                ]);
            }
        }

        // Sell history
        $selectedUsers = array_rand($users, min(10, count($users)));
        if (!is_array($selectedUsers)) {
            $selectedUsers = [$selectedUsers];
        }
        
        foreach ($selectedUsers as $userIndex) {
            $user = $users[$userIndex];
            $userAssets = Asset::where('user_id', $user->id)->get();
            
            foreach ($userAssets->take(3) as $asset) {
                $maxQuantity = min(50, $asset->quantity);
                $quantity = rand(10000, (int)($maxQuantity * 100000)) / 100000;
                $price = $asset->buy_price * 1.1;
                $amount = $quantity * $price;

                BeanHistory::create([
                    'user_id' => $user->id,
                    'asset_id' => $asset->id,
                    'batch_id' => $asset->batch_id,
                    'product_id' => $asset->product_id,
                    'quantity' => $quantity,
                    'item_unit_id' => $asset->item_unit_id,
                    'amount' => $amount,
                    'currency_id' => $asset->currency_id,
                    'charge' => 0,
                    'vat' => 0,
                    'trx' => getTrx(),
                    'type' => Status::SELL_HISTORY,
                    'created_at' => now()->subMonths(rand(0, 6))->subDays(rand(0, 30)),
                ]);
            }
        }
    }

    private function seedMarketPriceHistory($products)
    {
        foreach ($products as $product) {
            // Create price history for the last 6 months
            $basePrice = $product->market_price ?? 100;
            
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays(30 - $i);
                $price = $basePrice + (rand(-2000, 2000) / 100);
                
                MarketPriceHistory::create([
                    'product_id' => $product->id,
                    'market_price' => max(10, $price), // Ensure price is positive
                    'created_at' => $date,
                ]);
            }
        }
    }

    private function clearData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        MarketPriceHistory::truncate();
        BeanHistory::truncate();
        UserSellOrder::truncate();
        BatchSellOrder::truncate();
        Asset::truncate();
        Batch::truncate();
        Transaction::truncate();
        Deposit::truncate();
        Withdrawal::truncate();
        Wallet::truncate();
        User::truncate();
        Admin::truncate();
        Product::truncate();
        Warehouse::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

