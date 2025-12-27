<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Frontend;
use App\Models\HistoricalPrice;
use App\Models\Language;
use App\Models\MarketPriceHistory;
use App\Models\Page;
use App\Models\Product;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function index()
    {
        $pageTitle   = 'Home';
        $sections    = Page::where('tempname', activeTemplate())->where('slug', '/')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::home', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function beanPrice(Request $request)
    {
        $days = $request->days;

        if ($days > 365) {
            $format = '%Y';
        } else if ($days > 90) {
            $format = '%Y-%m';
        } else {
            $format = '%Y-%m-%d';
        }

        $historicalPrice = HistoricalPrice::where('date', '>=', now()->subDays($days));

        $beanPrice   = (clone $historicalPrice)->selectRaw("DATE_FORMAT(date, '$format') as price_date, MAX(price) as max_price")->groupBy('price_date')->get();
        $minMaxPrice = (clone $historicalPrice)->selectRaw("MIN(price) as min_price, MAX(price) as max_price")->first();

        $firstLastPrice = (clone $historicalPrice)->whereRaw("
        date IN (
            SELECT MIN(date) FROM historical_prices WHERE date >= ?
            UNION
            SELECT MAX(date) FROM historical_prices WHERE date >= ?
        )
    ", [now()->subDays($days), now()->subDays($days)])->get()->pluck('price');

        $firstPrice = $firstLastPrice[0];
        $lastPrice  = $firstLastPrice[1];

        $amountChangeDirection = $firstPrice <= $lastPrice ? 'up' : 'down';
        $amountChange          = showAmount(abs($lastPrice - $firstPrice), currencyFormat: false);
        $percentage            = showAmount($amountChange / $firstPrice * 100, currencyFormat: false) . '%';

        return responseSuccess('bean_price', 'Bean Price', ['bean_price' => $beanPrice, 'max_price' => showAmount($minMaxPrice->max_price), 'min_price' => showAmount($minMaxPrice->min_price), 'first_price' => showAmount($firstPrice), 'last_price' => showAmount($lastPrice), 'amount_change_direction' => $amountChangeDirection, 'amount_change' => $amountChange, 'percentage' => $percentage]);
    }

    public function getMarketPrices()
    {
        // جلب جميع المنتجات النشطة
        $products = Product::where('status', Status::ENABLE)->with('unit')->get();
        
        $chartData = [];
        
        foreach ($products as $product) {
            // جلب جميع أسعار المنتج مرتبة من الأقدم للأحدث
            $allPriceHistory = MarketPriceHistory::where('product_id', $product->id)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // إذا لم يكن هناك تاريخ أسعار، استخدم السعر الحالي
            if ($allPriceHistory->isEmpty()) {
                $currentPrice = (float) ($product->market_price ?? 0);
                $hourlyPrices = array_fill(0, 24, $currentPrice);
            } else {
                // تجميع الأسعار حسب الساعة لليوم الحالي
                $hourlyPrices = [];
                
                // أول سعر معروف كقيمة افتراضية
                $lastKnownPrice = (float) $allPriceHistory->first()->market_price;
                
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourStart = now()->startOfDay()->addHours($hour);
                    $hourEnd = $hourStart->copy()->addHour();
                    
                    // البحث عن آخر سعر تم تسجيله قبل نهاية هذه الساعة
                    $relevantPrice = null;
                    
                    foreach ($allPriceHistory as $price) {
                        // إذا كان السعر تم تسجيله قبل أو خلال هذه الساعة
                        if ($price->created_at < $hourEnd) {
                            $relevantPrice = $price;
                            $lastKnownPrice = (float) $price->market_price;
                        }
                    }
                    
                    // استخدم آخر سعر معروف لهذه الساعة
                    $hourlyPrices[] = $lastKnownPrice;
                }
            }
            
            $chartData[] = [
                'name' => $product->name,
                'data' => $hourlyPrices,
                'unit' => $product->unit ? $product->unit->name : '',
                'current_price' => (float) ($product->market_price ?? 0)
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $chartData,
            'debug' => [
                'current_time' => now()->toDateTimeString(),
                'day_start' => now()->startOfDay()->toDateTimeString(),
            ]
        ]);
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'error',
                'message' => $validator->errors()->all(),
            ]);
        }

        $subscribe        = new Subscriber();
        $subscribe->email = $request->email;
        $subscribe->save();

        $notify = 'Thank you, we will notice you our latest news';

        return response()->json([
            'code'    => 200,
            'status'  => 'success',
            'message' => $notify,
        ]);
    }

    public function pages($slug)
    {
        $page        = Page::where('tempname', activeTemplate())->where('slug', $slug)->firstOrFail();
        $pageTitle   = $page->name;
        $sections    = $page->secs;
        $seoContents = $page->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::pages', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function faq()
    {
        $pageTitle   = "FAQ";
        $faqContent  = Frontend::where('data_keys', 'faq.content')->where('tempname', activeTemplateName())->orderBy('id', 'desc')->first();
        $faqElement  = Frontend::where('data_keys', 'faq.element')->where('tempname', activeTemplateName())->orderBy('id', 'desc')->paginate(getPaginate(9));
        $sections    = Page::where('tempname', activeTemplate())->where('slug', 'faq')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::faq', compact('pageTitle', 'faqContent', 'faqElement', 'sections', 'seoContents', 'seoImage'));
    }

    public function contact()
    {
        $pageTitle   = "Contact Us";
        $user        = auth()->user();
        $sections    = Page::where('tempname', activeTemplate())->where('slug', 'contact')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::contact', compact('pageTitle', 'user', 'sections', 'seoContents', 'seoImage'));
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name'    => 'required',
            'email'   => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket           = new SupportTicket();
        $ticket->user_id  = auth()->id() ?? 0;
        $ticket->name     = $request->name;
        $ticket->email    = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;

        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message           = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug)
    {
        $policy      = Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle   = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage    = @$seoContents->image ? frontendImage('policy_pages', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) {
            $lang = 'en';
        }

        session()->put('lang', $lang);
        return back();
    }

    public function blogs()
    {
        $pageTitle   = "Blogs";
        $sections    = Page::where('tempname', activeTemplate())->where('slug', 'blog')->first();
        $blogs       = Frontend::where('data_keys', 'blog.element')->where('tempname', activeTemplateName())->orderBy('id', 'desc')->paginate(getPaginate(9));
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::blogs', compact('pageTitle', 'sections', 'blogs', 'seoContents', 'seoImage'));
    }

    public function blogDetails($slug)
    {
        $blog        = Frontend::where('slug', $slug)->where('data_keys', 'blog.element')->firstOrFail();
        $blogs       = Frontend::where('id', '!=', $blog->id)->where('data_keys', 'blog.element')->latest()->limit(5)->get();
        $pageTitle   = $blog->data_values->title;
        $seoContents = $blog->seo_content;
        $seoImage    = @$seoContents->image ? frontendImage('blog', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::blog_details', compact('blog', 'pageTitle', 'seoContents', 'seoImage', 'blogs'));
    }

    public function cookieAccept()
    {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy()
    {
        $cookieContent = Frontend::where('data_keys', 'cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie    = Frontend::where('data_keys', 'cookie.data')->first();
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null)
    {
        $imgWidth  = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text      = $imgWidth . '×' . $imgHeight;
        $fontFile  = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize  = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox    = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance()
    {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

}
