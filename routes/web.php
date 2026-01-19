<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function(){
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

// Clear cache route - runs config:clear and cache:clear
// Usage: /clear-cache?token=your-secret-token
Route::get('/clear-cache', function(\Illuminate\Http\Request $request){
    // Optional token protection - if CACHE_CLEAR_TOKEN is set in .env, require it
    $expectedToken = env('CACHE_CLEAR_TOKEN');
    
    if ($expectedToken) {
        $token = $request->get('token') ?? $request->header('X-Cache-Token');
        
        if ($token !== $expectedToken) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid or missing token.'
            ], 401);
        }
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully',
            'commands' => [
                'config:clear' => 'executed',
                'cache:clear' => 'executed'
            ],
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toDateTimeString()
        ], 500);
    }
})->name('clear.cache');

// Deployment endpoints (protected with token)
Route::prefix('deploy')->name('deploy.')->withoutMiddleware('maintenance')->group(function () {
    Route::post('setup-env', 'DeployController@setupEnv')->name('setup.env');
    Route::post('composer-install', 'DeployController@composerInstall')->name('composer.install');
    Route::post('migrate', 'DeployController@migrate')->name('migrate');
    Route::post('clear-cache', 'DeployController@clearCache')->name('clear.cache');
});

Route::get('cron', 'CronController@cron')->name('cron');

// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller('SiteController')->group(function () {
    Route::get('bean-price', 'beanPrice')->name('bean.price');
    Route::get('market-prices', 'getMarketPrices')->name('market.prices');

    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::post('/subscribe', 'subscribe')->name('subscribe');
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');
    Route::get('blogs', 'blogs')->name('blogs');

    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode','maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('faq', 'faq')->name('faq');
    Route::get('debug-email', 'debugEmail')->withoutMiddleware('maintenance')->name('debug.email');
    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});
