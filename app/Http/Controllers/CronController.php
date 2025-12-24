<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Lib\PriceProvider;
use App\Models\Category;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\HistoricalPrice;
use App\Models\PriceApi;
use Carbon\Carbon;
use Exception;

class CronController extends Controller
{
    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }

    public function priceUpdate()
    {
        try {
            $api = PriceApi::active()->first();
            if (!$api) {
                throw new \Exception('Price API not found');
            }

            $priceData = PriceProvider::getPriceData($api);
            $priceData = json_decode($priceData->getContent());

            if ($priceData->status != 'success') {
                throw new \Exception($priceData->message->errors[0]);
            }

            $priceData    = $priceData->data;
            $pricePerGram = $priceData->price / 31.1034768;

            $categories = Category::get();

            foreach ($categories as $category) {
                $category->price      = $pricePerGram / 24 * $category->karat;
                $category->change_1h  = $priceData->change_1h;
                $category->change_24h = $priceData->change_24h;
                $category->change_7d  = $priceData->change_7d;
                $category->change_30d = $priceData->change_30d;
                $category->change_90d = $priceData->change_90d;
                $category->save();
            }

            cache()->put('last_price', $pricePerGram);
            $this->updateHistoricalPrice($pricePerGram);

        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function updateHistoricalPrice($pricePerGram)
    {
        $historicalPrice = HistoricalPrice::where('date', today())->first();

        if (!$historicalPrice) {
            $historicalPrice        = new HistoricalPrice();
            $historicalPrice->date  = today();
            $historicalPrice->price = $pricePerGram;
        } else {
            $historicalPrice->price = $pricePerGram > $historicalPrice->price ? $pricePerGram : $historicalPrice->price;
        }

        $historicalPrice->save();
    }

    /**
     * تسجيل أسعار السوق كل ساعة إذا لم توجد سجلات
     */
    public function recordHourlyMarketPrices()
    {
        try {
            $currentHourStart = now()->startOfHour();
            $currentHourEnd = now()->endOfHour();
            
            $products = \App\Models\Product::active()->get();
            $recordedCount = 0;
            
            foreach ($products as $product) {
                // التحقق من وجود سجل في الساعة الحالية
                $existingRecord = \App\Models\MarketPriceHistory::where('product_id', $product->id)
                    ->whereBetween('created_at', [$currentHourStart, $currentHourEnd])
                    ->exists();
                
                // إذا لم يوجد سجل، أضف السعر الحالي
                if (!$existingRecord && $product->market_price !== null && $product->market_price > 0) {
                    \App\Models\MarketPriceHistory::create([
                        'product_id' => $product->id,
                        'market_price' => $product->market_price,
                    ]);
                    $recordedCount++;
                }
            }
            
            \Log::info("Recorded hourly market prices for {$recordedCount} products.");
        } catch (Exception $ex) {
            \Log::error("Error recording hourly market prices: " . $ex->getMessage());
            throw new \Exception($ex->getMessage());
        }
    }

}
