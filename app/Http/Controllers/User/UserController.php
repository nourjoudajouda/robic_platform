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
use App\Models\GoldHistory;
use App\Models\HistoricalPrice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function home()
    {
        $pageTitle     = 'Dashboard';
        $user          = auth()->user();
        $portfolioData = $this->getPortfolioData();
        $assets        = Asset::with('category')->where('user_id', $user->id)->get();
        $assetValue    = $assets->sum(fn($asset) => $asset->quantity * $asset->category->price);
        $chargeLimit   = ChargeLimit::where('slug', 'buy')->first();
        $categories    = Category::active()->get();

        $giftRedeems = GoldHistory::redeemOrGift()->where('user_id', $user->id)->orderBy('id', 'desc')->limit(5)->get();
        $buySells    = GoldHistory::buyOrSell()->where('user_id', $user->id)->orderBy('id', 'desc')->limit(6)->get();

        return view('Template::user.dashboard', compact('pageTitle', 'portfolioData', 'assets', 'assetValue', 'user', 'chargeLimit', 'categories', 'giftRedeems', 'buySells'));
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

        $assets        = Asset::with('category')->where('user_id', $user->id)->get();
        $assetLogs     = GoldHistory::where('user_id', $user->id)->limit(5)->orderBy('id', 'desc')->get();
        $portfolioData = $this->getPortfolioData();

        return view('Template::user.portfolio', compact('pageTitle', 'assets', 'portfolioData', 'assetLogs'));
    }

    public function assetLogs()
    {
        $pageTitle = 'Asset Logs';
        $assetLogs = GoldHistory::where('user_id', auth()->user()->id);
        if (request()->has('asset_id')) {
            $assetLogs = $assetLogs->where('asset_id', request()->asset_id);
        }
        if (request()->data == 'redeem-gift') {
            $assetLogs = $assetLogs->redeemOrGift();
        }
        if (request()->data == 'buy-sell') {
            $assetLogs = $assetLogs->buyOrSell();
        }
        $assetLogs = $assetLogs->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.asset_log', compact('pageTitle', 'assetLogs'));
    }

    public function priceTracker(Request $request)
    {
        $pageTitle = 'Price Tracker';
        $days      = $request->days ?? 90;
        $prices    = HistoricalPrice::where('date', '>=', now()->subDays($days))->groupBy('date')->orderBy('date', 'asc')->get();

        $categories = Category::active()->get();
        $category   = $categories->find($request->category) ?? $categories->first();

        // إذا لم توجد فئات، أنشئ بيانات افتراضية
        if (!$category) {
            $prices = collect([]);
            return view('Template::user.price_tracker', compact('pageTitle', 'prices', 'categories', 'category'));
        }

        $prices = $prices->map(function ($price) use ($category) {
            return [
                'date'  => $price->date,
                'price' => getAmount($price->price * $category->karat / 24),
            ];
        });


        if ($request->ajax()) {
            $column        = 'change_' . $days . 'd';
            $percentChange = $category->$column ?? 0;
            $priceChange   = $percentChange > 0 ? $category->price * $percentChange / 100 : $category->price * abs($percentChange) / 100;
            $data          = [
                'prices'         => $prices,
                'percent_change' => $percentChange,
                'price_change'   => $priceChange,
            ];
            return response()->json($data);
        }

        return view('Template::user.price_tracker', compact('pageTitle', 'prices', 'categories', 'category'));
    }

    private function getPortfolioData()
    {
        $user                  = auth()->user();
        $assetData             = Asset::with('category')->where('user_id', $user->id)->get();
        $assetCategoryName     = $assetData->pluck('category.name');
        $totalAssetQuantity    = $assetData->sum('quantity');
        $assetCategoryQuantity = $assetData->pluck('quantity');
        if ($totalAssetQuantity > 0) {
            $assetCategoryQuantity = $assetCategoryQuantity->map(function ($quantity) use ($totalAssetQuantity) {
                return getAmount($quantity / $totalAssetQuantity * 100);
            });
        }
        return [
            'assets_category_name'    => $assetCategoryName,
            'total_asset_quantity'    => $totalAssetQuantity,
            'asset_category_quantity' => $assetCategoryQuantity,
        ];
    }
}
