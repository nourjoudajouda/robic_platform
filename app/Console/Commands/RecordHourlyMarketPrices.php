<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\MarketPriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecordHourlyMarketPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:record-hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record hourly market prices if no records exist for current hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking market price records for current hour...');
        
        // الساعة الحالية (بداية الساعة)
        $currentHourStart = now()->startOfHour();
        $currentHourEnd = now()->endOfHour();
        
        // جلب جميع المنتجات النشطة
        $products = Product::active()->get();
        
        if ($products->isEmpty()) {
            $this->warn('No active products found.');
            return 0;
        }
        
        $recordedCount = 0;
        
        foreach ($products as $product) {
            // التحقق من وجود سجل لهذا المنتج في الساعة الحالية
            $existingRecord = MarketPriceHistory::where('product_id', $product->id)
                ->whereBetween('created_at', [$currentHourStart, $currentHourEnd])
                ->exists();
            
            // إذا لم يوجد سجل، أضف سعر السوق الحالي
            if (!$existingRecord && $product->market_price !== null && $product->market_price > 0) {
                MarketPriceHistory::create([
                    'product_id' => $product->id,
                    'market_price' => $product->market_price,
                ]);
                
                $recordedCount++;
                $this->info("Recorded price for: {$product->name} - Price: {$product->market_price}");
            }
        }
        
        $this->info("Successfully recorded prices for {$recordedCount} products.");
        
        return 0;
    }
}

