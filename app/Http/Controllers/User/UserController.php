<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\Asset;
use App\Models\Category;
use App\Models\ChargeLimit;
use App\Models\DeviceToken;
use App\Models\Form;
use App\Models\BeanHistory;
use App\Models\HistoricalPrice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function home(Request $request)
    {
        $pageTitle     = 'Dashboard';
        $user          = auth()->user();
        $portfolioData = $this->getPortfolioData();
        $assets        = Asset::with('batch.product')->where('user_id', $user->id)->where('quantity', '>', 0)->get();
        // القيمة الحالية بسعر السوق
        $assetValue    = $assets->sum(fn($asset) => $asset->quantity * ($asset->batch->sell_price ?? 0));
        $chargeLimit   = ChargeLimit::where('slug', 'buy')->first();

        $giftRedeems = BeanHistory::redeemOrGift()->where('user_id', $user->id)->with('batch.product.unit')->orderBy('id', 'desc')->limit(5)->get();
        $buySells    = BeanHistory::buyOrSell()->where('user_id', $user->id)->with('batch.product.unit')->orderBy('id', 'desc')->limit(6)->get();

        // بيانات التشارت (نفس منطق price tracker)
        $days = $request->days ?? 90; // الافتراضي 90 يوم
        $priceFrom = gs('chart_price_from') ?? 0;
        $priceTo = gs('chart_price_to') ?? 20;
        $products = \App\Models\Product::active()->with(['unit', 'currency'])->get();
        
        $allProductsData = [];
        $labels = collect();
        
        foreach ($products as $product) {
            if ($days == 1) {
                // عرض آخر 24 ساعة (hourly)
                $priceHistory = \App\Models\MarketPriceHistory::where('product_id', $product->id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $lastKnownPrice = $product->market_price ?? (($priceFrom + $priceTo) / 2);
                $startOfDay = now()->startOfDay();
                $productData = [];
                
                // ملء البيانات لكل ساعة
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourStart = $startOfDay->copy()->addHours($hour);
                    $hourEnd = $hourStart->copy()->addHour();
                    
                    $priceInHour = $priceHistory->filter(function($record) use ($hourStart, $hourEnd) {
                        return $record->created_at >= $hourStart && $record->created_at < $hourEnd;
                    })->first();
                    
                    if ($priceInHour) {
                        $lastKnownPrice = $priceInHour->market_price;
                    }
                    
                    $productData[] = round($lastKnownPrice, 2);
                    
                    if ($labels->count() < 24) {
                        $labels->push($hour);
                    }
                }
            } else {
                // عرض حسب الأيام (daily)
                $priceHistory = \App\Models\MarketPriceHistory::where('product_id', $product->id)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $lastKnownPrice = $product->market_price ?? (($priceFrom + $priceTo) / 2);
                $productData = [];
                
                // ملء البيانات لكل يوم
                for ($day = $days - 1; $day >= 0; $day--) {
                    $dayStart = now()->subDays($day)->startOfDay();
                    $dayEnd = $dayStart->copy()->endOfDay();
                    
                    $priceInDay = $priceHistory->filter(function($record) use ($dayStart, $dayEnd) {
                        return $record->created_at >= $dayStart && $record->created_at <= $dayEnd;
                    })->last(); // آخر سعر في اليوم
                    
                    if ($priceInDay) {
                        $lastKnownPrice = $priceInDay->market_price;
                    }
                    
                    $productData[] = round($lastKnownPrice, 2);
                    
                    if ($labels->count() < $days) {
                        $labels->push($dayStart->format('Y-m-d'));
                    }
                }
            }
            
            $allProductsData[] = [
                'name' => $product->name,
                'data' => $productData,
                'unit' => $product->unit ? $product->unit->name : 'unit',
                'current_price' => $product->market_price ?? 0
            ];
        }

        return view('Template::user.dashboard', compact('pageTitle', 'portfolioData', 'assets', 'assetValue', 'user', 'chargeLimit', 'giftRedeems', 'buySells', 'allProductsData', 'labels', 'priceFrom', 'priceTo', 'products', 'days'));
    }

    public function getPriceHistory()
    {
        $days   = request()->days;
        $prices = HistoricalPrice::where('date', '>=', now()->subDays($days))->groupBy('date')->orderBy('date', 'ASC')->get();

        $dates  = $prices->pluck('date')->toArray();
        $prices = $prices->pluck('price')->map(fn($price) => getAmount($price))->toArray();

        return compact('dates', 'prices');
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $pageTitle = '2FA Security';
        return view('Template::user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'key'  => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts  = Status::ENABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = Status::DISABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions()
    {
        $pageTitle = 'Transactions';
        $remarks   = Transaction::distinct('remark')->orderBy('remark')->get('remark');

        $transactions = Transaction::where('user_id', auth()->id())->searchable(['trx'])->filter(['trx_type', 'remark'])->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'kyc')->first();
        return view('Template::user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData()
    {
        $user      = auth()->user();
        $pageTitle = 'KYC Data';
        abort_if($user->kv == Status::VERIFIED, 403);
        return view('Template::user.kyc.info', compact('pageTitle', 'user'));
    }

    public function kycSubmit(Request $request)
    {
        $form           = Form::where('act', 'kyc')->firstOrFail();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $user = auth()->user();
        foreach (@$user->kyc_data ?? [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $userData                   = $formProcessor->processFormData($request, $formData);
        $user->kyc_data             = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv                   = Status::KYC_PENDING;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);

    }

    public function userData()
    {
        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $pageTitle  = 'User Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::user.user_data', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
        ]);

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->username     = $request->username;

        $user->address      = $request->address;
        $user->city         = $request->city;
        $user->state        = $request->state;
        $user->zip          = $request->zip;
        $user->country_name = @$request->country;
        $user->dial_code    = $request->mobile_code;

        $user->profile_complete = Status::YES;
        $user->save();

        return to_route('user.home');
    }

    public function addDeviceToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function portfolio()
    {
        $pageTitle = 'Portfolio';
        $user      = auth()->user();

        // جلب جميع الـ assets للمستخدم
        $allAssets = Asset::with('batch.product.unit', 'batch.product.currency', 'product.unit', 'product.currency')
            ->where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->get();
        
        // تجميع الـ assets حسب product_id
        $groupedAssets = $allAssets->groupBy('product_id')->map(function($productAssets) use ($user) {
            $firstAsset = $productAssets->first();
            $product = $firstAsset->product ?? $firstAsset->batch->product;
            
            // حساب الكمية المتاحة الفعلية (بعد خصم الكميات المستخدمة في sell orders نشطة)
            $totalQuantity = $productAssets->sum('quantity');
            $totalUsedQuantity = 0;
            $totalSoldQuantity = 0;
            
            foreach ($productAssets as $asset) {
                // حساب الكمية المستخدمة في sell orders نشطة (نستخدم quantity وليس available_quantity)
                // لأن quantity هي الكمية الأصلية المخصصة للبيع
                $usedQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                    ->where('status', Status::SELL_ORDER_ACTIVE)
                    ->sum('quantity'); // استخدام quantity وليس available_quantity
                $totalUsedQuantity += $usedQuantity;
                
                // حساب الكمية المباعة (status = SELL_ORDER_SOLD)
                $soldQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                    ->where('status', Status::SELL_ORDER_SOLD)
                    ->sum('quantity');
                $totalSoldQuantity += $soldQuantity;
            }
            
            $availableQuantity = max(0, $totalQuantity - $totalUsedQuantity - $totalSoldQuantity);
            
            // التحقق إذا كان المنتج تم بيعه بالكامل (جميع الكميات في sell orders مباعة)
            $isFullySold = ($totalQuantity > 0 && $totalSoldQuantity >= $totalQuantity) || 
                           ($availableQuantity == 0 && $totalSoldQuantity > 0);
            
            // جلب آخر سعر سوق من market_price_history
            $latestMarketPrice = \App\Models\MarketPriceHistory::where('product_id', $firstAsset->product_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $currentMarketPrice = $latestMarketPrice ? $latestMarketPrice->market_price : 0;
            
            return (object)[
                'product_id' => $firstAsset->product_id,
                'product' => $product,
                'total_quantity' => $totalQuantity, // الكمية الإجمالية
                'available_quantity' => $availableQuantity, // الكمية المتاحة الفعلية
                'used_quantity' => $totalUsedQuantity, // الكمية المستخدمة في sell orders نشطة
                'sold_quantity' => $totalSoldQuantity, // الكمية المباعة
                'is_fully_sold' => $isFullySold, // flag للتحقق إذا تم البيع بالكامل
                'total_value' => $productAssets->sum(function($asset) {
                    // استخدام buy_price (سعر الشراء) وليس sell_price (السعر الحالي)
                    return $asset->quantity * ($asset->buy_price ?? 0);
                }),
                'current_market_value' => $availableQuantity * $currentMarketPrice, // استخدام الكمية المتاحة فقط
                'current_market_price' => $currentMarketPrice, // حفظ السعر للعرض
                'batches' => $productAssets, // كل الـ batches للمنتج
                'batches_count' => $productAssets->count(),
            ];
        });
        
        $assetLogs     = BeanHistory::where('user_id', $user->id)->with('batch.product.unit')->limit(5)->orderBy('id', 'desc')->get();
        $portfolioData = $this->getPortfolioData();

        return view('Template::user.portfolio', compact('pageTitle', 'groupedAssets', 'portfolioData', 'assetLogs'));
    }

    public function assetLogs()
    {
        $pageTitle = 'Asset Logs';
        $assetLogs = BeanHistory::where('user_id', auth()->user()->id);
        if (request()->has('asset_id')) {
            $assetLogs = $assetLogs->where('asset_id', request()->asset_id);
        }
        if (request()->data == 'redeem-gift') {
            $assetLogs = $assetLogs->redeemOrGift();
        }
        if (request()->data == 'buy-sell') {
            $assetLogs = $assetLogs->buyOrSell();
        }
        $assetLogs = $assetLogs->with(['batch.product.unit', 'batch.product.currency', 'itemUnit', 'currency'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.asset_log', compact('pageTitle', 'assetLogs'));
    }

    public function priceTracker(Request $request)
    {
        $pageTitle = 'Price Tracker';
        $days = $request->days ?? 1; // الافتراضي 1 يوم (24 ساعة)
        
        // الحصول على نطاق الأسعار من الإعدادات
        $priceFrom = gs('chart_price_from') ?? 0;
        $priceTo = gs('chart_price_to') ?? 20;
        
        // جلب جميع المنتجات النشطة
        $products = \App\Models\Product::active()->with(['unit', 'currency'])->get();
        
        $allProductsData = [];
        $labels = collect();
        
        foreach ($products as $product) {
            if ($days == 1) {
                // عرض آخر 24 ساعة (hourly)
                $priceHistory = \App\Models\MarketPriceHistory::where('product_id', $product->id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $lastKnownPrice = $product->market_price ?? (($priceFrom + $priceTo) / 2);
                $startOfDay = now()->startOfDay();
                $productData = [];
                
                // ملء البيانات لكل ساعة
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourStart = $startOfDay->copy()->addHours($hour);
                    $hourEnd = $hourStart->copy()->addHour();
                    
                    $priceInHour = $priceHistory->filter(function($record) use ($hourStart, $hourEnd) {
                        return $record->created_at >= $hourStart && $record->created_at < $hourEnd;
                    })->first();
                    
                    if ($priceInHour) {
                        $lastKnownPrice = $priceInHour->market_price;
                    }
                    
                    $productData[] = round($lastKnownPrice, 2);
                    
                    if ($labels->count() < 24) {
                        $labels->push($hour);
                    }
                }
            } else {
                // عرض حسب الأيام (daily)
                $priceHistory = \App\Models\MarketPriceHistory::where('product_id', $product->id)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $lastKnownPrice = $product->market_price ?? (($priceFrom + $priceTo) / 2);
                $productData = [];
                
                // ملء البيانات لكل يوم
                for ($day = $days - 1; $day >= 0; $day--) {
                    $dayStart = now()->subDays($day)->startOfDay();
                    $dayEnd = $dayStart->copy()->endOfDay();
                    
                    $priceInDay = $priceHistory->filter(function($record) use ($dayStart, $dayEnd) {
                        return $record->created_at >= $dayStart && $record->created_at <= $dayEnd;
                    })->last(); // آخر سعر في اليوم
                    
                    if ($priceInDay) {
                        $lastKnownPrice = $priceInDay->market_price;
                    }
                    
                    $productData[] = round($lastKnownPrice, 2);
                    
                    if ($labels->count() < $days) {
                        $labels->push($dayStart->format('Y-m-d'));
                    }
                }
            }
            
            $allProductsData[] = [
                'name' => $product->name,
                'data' => $productData,
                'unit' => $product->unit ? $product->unit->name : 'unit',
                'current_price' => $product->market_price ?? 0
            ];
        }

        if ($request->ajax()) {
            return response()->json([
                'products' => $allProductsData,
                'labels' => $labels,
                'days' => $days
            ]);
        }

        return view('Template::user.price_tracker', compact('pageTitle', 'allProductsData', 'labels', 'priceFrom', 'priceTo', 'products', 'days'));
    }

    private function getPortfolioData()
    {
        $user                  = auth()->user();
        $assetData             = Asset::with('batch.product', 'product')->where('user_id', $user->id)->where('quantity', '>', 0)->get();
        
        // تجميع الـ assets حسب product_id لحساب النسب بشكل صحيح
        $groupedByProduct = $assetData->groupBy('product_id')->map(function($productAssets) use ($user) {
            $firstAsset = $productAssets->first();
            $product = $firstAsset->product ?? $firstAsset->batch->product;
            
            // حساب الكمية المتاحة الفعلية (بعد خصم الكميات المستخدمة في sell orders نشطة والمباعة)
            $totalQuantity = $productAssets->sum('quantity');
            $totalUsedQuantity = 0;
            $totalSoldQuantity = 0;
            
            foreach ($productAssets as $asset) {
                // حساب الكمية المستخدمة في sell orders نشطة (نستخدم quantity وليس available_quantity)
                // لأن quantity هي الكمية الأصلية المخصصة للبيع
                $usedQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                    ->where('status', Status::SELL_ORDER_ACTIVE)
                    ->sum('quantity'); // استخدام quantity وليس available_quantity
                $totalUsedQuantity += $usedQuantity;
                
                // حساب الكمية المباعة (status = SELL_ORDER_SOLD)
                $soldQuantity = \App\Models\UserSellOrder::where('asset_id', $asset->id)
                    ->where('status', Status::SELL_ORDER_SOLD)
                    ->sum('quantity');
                $totalSoldQuantity += $soldQuantity;
            }
            
            $availableQuantity = max(0, $totalQuantity - $totalUsedQuantity - $totalSoldQuantity);
            
            return [
                'name' => $product->name ?? 'N/A',
                'quantity' => $availableQuantity, // استخدام الكمية المتاحة فقط (باستثناء المباعة)
            ];
        })->filter(function($product) {
            // إخفاء المنتجات التي تم بيعها بالكامل من الـ chart (الكمية المتاحة = 0)
            // لكن سيتم عرضها في قائمة المنتجات مع علامة Sold
            return $product['quantity'] > 0;
        });
        
        $assetProductName      = $groupedByProduct->pluck('name');
        $totalAssetQuantity    = $groupedByProduct->sum('quantity');
        $assetProductQuantity  = $groupedByProduct->pluck('quantity');
        
        if ($totalAssetQuantity > 0) {
            $assetProductQuantity = $assetProductQuantity->map(function ($quantity) use ($totalAssetQuantity) {
                return getAmount($quantity / $totalAssetQuantity * 100);
            });
        }
        
        return [
            'assets_category_name'    => $assetProductName,
            'total_asset_quantity'    => $totalAssetQuantity,
            'asset_category_quantity' => $assetProductQuantity,
        ];
    }
}
