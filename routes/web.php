<?php

use Illuminate\Support\Facades\Route;

use App\Support\DomainManager;
use App\Support\QRCodeStorage;
use App\Support\SitemapGenerator;
use App\Support\DashboardAssetsServer;
use App\Support\Auth\Auth0\Auth0Manager;
use App\Http\Controllers\Auth0Controller;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\BulkOperationsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\GdprConsentController;
use App\Http\Controllers\GrapesJsController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\InvoiceController;
use App\Support\ViewComposers\MainLayoutComposer;
use App\Http\Controllers\QRCodeRedirectController;
use App\Http\Controllers\QRCodeScanController;
use App\Http\Controllers\TroubleshootController;
use App\Models\LeadFormResponse;
use App\Models\User;
use App\Notifications\Dynamic\LeadFormResponseNotification;
use App\Providers\RouteServiceProvider;
use App\Repositories\BlogPostManager;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon\FileServer as QRCodeFaviconFileServer;
use App\Support\System\AdminHelper;
use Dedoc\Scramble\Scramble;

Route::get('/speed-test', [BenchmarkController::class, 'showTime']);

Route::get('/speed-test/db', [BenchmarkController::class, 'runQuery']);

Route::get('/troubleshoot', [TroubleshootController::class, 'home']);

Route::get('/fix-admin', function () {
    return AdminHelper::makeAdminAccount(
        name: 'Mohammad',
        email: 'mohammad.a.alhomsi@gmail.com',
        password: 'PPdkfieuwDu_38',
    );
});

Route::get('/preview-email', function () {
    return LeadFormResponseNotification::instance(
        LeadFormResponse::first()
    )->toMail(
        User::find(1)
    )->render();
});


Route::middleware('custom_frontend_redirector')->group(function () {

    Route::get('/', HomePageController::class);

    Route::get('/payment/success', [
        CheckoutController::class,
        'paymentSuccess'
    ])->name('payment.success');

    Route::get(
        '/payment/thankyou',
        [CheckoutController::class, 'paymentThankyou']
    )->name('payment.thankyou');

    Route::get(
        '/payment/canceled',
        [CheckoutController::class, 'paymentCanceled']
    )->name('payment.canceled');

    Route::get(
        '/payment/invalid',
        [CheckoutController::class, 'paymentInvalid']
    )->name('payment.invalid');



    BlogPostManager::defineRoutes();
});


Route::get('/account-credit-cart', function () {
    return view('blue.pages.account-credit-cart');
});

Route::get('/language/{locale}', [TranslationController::class, 'changeLanguage']);

Route::get('/dashboard/qrcodes/designer/preview', function () {
    return view('qrcode.designer-preview');
});

Route::get(
    '/bulk-operations/print-instance/{instance}',
    [BulkOperationsController::class, 'print']
);

Route::get('/{frontend}', function () {
    return view('blue.pages.dashboard');
})->where('frontend', MainLayoutComposer::PATTERN_PWA_ROUTES);

// Keeping this route for backword compatiblity.

QRCodeRedirectController::bindRoutes();

PaymentProcessorManager::registerWebRoutes();

Route::get('/sitemap.xml', function () {
    return response()->view('sitemap', [
        'urls' => SitemapGenerator::generate()
    ])->header('content-type', 'application/xml; charset="utf8"');
});

Route::get('/email/verify/{id}/{hash}', [AccountController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

RouteServiceProvider::registerLoginRoute();

Route::get(
    '/magic-login/{user}',
    [AccountController::class, 'magicLogin']
)->name('magic-login');

Route::get('/system/cron', [SystemController::class, 'cron'])->name('cron');

Route::get('/robots.txt', function () {
    return view('robots');
});

Route::get('/' . DomainManager::DOMAIN_CONNECTION_ROUTE, function () {
    $domainManager = new DomainManager();

    return $domainManager->connectionString();
});

Route::get(Auth0Manager::loginUrl(), [Auth0Controller::class, 'login']);

Route::get(Auth0Manager::logoutUrl(), [Auth0Controller::class, 'logout']);

Route::get(Auth0Manager::callbackUrl(), [Auth0Controller::class, 'handleCallback']);

Route::get('/set-cookie-consent', [GdprConsentController::class, 'setCookieConsent']);

QRCodeStorage::registerDirectSvgRoute();

Route::get('/auto-update', function () {
    if (file_exists(public_path('update.php'))) {
        require_once public_path('update.php');
        die;
    } else {
        abort(404, 'Page not found');
    }
});

Route::get(
    QRCodeFaviconFileServer::ROUTE,
    [QRCodeController::class, 'serveFavicon']
);

Route::get(
    'system/cron/runner',
    [SystemController::class, 'cronRunner']
);

Route::get(
    '/website-builder',
    [GrapesJsController::class, 'viewWebsiteBuilderPage']
);

Route::get('/dynamic-style/{scan}', [QRCodeScanController::class, 'collectLanguage']);

Route::get(
    '/add-to-apple-wallet/{qrcode}',
    [QRCodeController::class, 'addToAppleWallet']
);

Route::get('/invoice/{uuid}', [InvoiceController::class, 'viewInvoice']);

// Feedback routes
Route::get('/feedback/{token}', [App\Http\Controllers\FeedbackController::class, 'showRatingPage'])->name('feedback.rating');
Route::post('/feedback/{token}/submit', [App\Http\Controllers\FeedbackController::class, 'submit'])->name('feedback.submit');

// Staff feedback routes (protected)
Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/feedback', [App\Http\Controllers\StaffFeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{id}', [App\Http\Controllers\StaffFeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/feedback/{id}/approve-reply', [App\Http\Controllers\StaffFeedbackController::class, 'approveReply'])->name('feedback.approve-reply');
    Route::post('/feedback/{id}/resolve', [App\Http\Controllers\StaffFeedbackController::class, 'markResolved'])->name('feedback.resolve');
    Route::get('/feedback/export', [App\Http\Controllers\StaffFeedbackController::class, 'export'])->name('feedback.export');
});

// Test route to generate feedback URL (for testing)
Route::get('/feedback', function() {
    $qrcode = \App\Models\QRCode::where('type', 'business-review')
        ->where('archived', false)
        ->where('status', \App\Models\QRCode::STATUS_ENABLED)
        ->first();
    
    if (!$qrcode) {
        return 'No business-review QR code found. Please create one first.';
    }
    
    $feedbackUrl = \App\Http\Controllers\FeedbackController::generateFeedbackUrl($qrcode);
    
    return view('feedback.test', [
        'qrcode' => $qrcode,
        'feedbackUrl' => $feedbackUrl,
    ]);
})->name('feedback.test');

DashboardAssetsServer::registerWebRoute();

Scramble::registerUiRoute(path: 'docs/api')->name('scramble.docs.ui');

Scramble::registerJsonSpecificationRoute(path: 'docs/api.json')->name('scramble.docs.document');
