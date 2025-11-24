<?php

use App\Models\User;
use App\Models\Page;
use App\Models\QRCode;
use App\Models\BlogPost;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiGeneratorController;
use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\BillingCollectionController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BulkOperationsController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentBlockController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomCodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\DynamicBioLinkBlockController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\GrapesJsController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadFormController;
use App\Http\Controllers\LeadFormResponseController;
use App\Http\Controllers\MarkdownController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PaymentProcessorController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PluginsController;
use App\Http\Controllers\QRCodeScanController;
use App\Http\Controllers\QRCodeTemplatesController;
use App\Http\Controllers\QRCodeTypeController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TemplateCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\WidgetsController;
use App\Http\Middleware\RemoveEmptyFieldsFromArrays;
use App\Models\Contact;
use App\Models\ContentBlock;
use App\Models\Currency;
use App\Models\CustomCode;
use App\Models\Domain;
use App\Models\DynamicBioLinkBlock;
use App\Models\File;
use App\Models\QRCodeTemplate;
use App\Models\Role;
use App\Models\TemplateCategory;
use App\Models\Translation;
use App\Models\Widget;
use App\Support\GoogleFonts;
use App\Support\QRCodeStorage;

require_once 'api/custom-forms.php';
require_once 'api/troubleshoot.php';

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/speed-test', [BenchmarkController::class, 'showTime']);


Route::prefix('/install')->group(function () {
    Route::post('/save', [InstallController::class, 'saveEnvVariables']);

    Route::post('/load', [InstallController::class, 'loadEnvVariables']);

    Route::post('/verify-database', [InstallController::class, 'verifyDatabase']);

    Route::post('/verify-mail', [InstallController::class, 'verifyMail']);

    Route::post('/verify-purchase-code', [InstallController::class, 'verifyPurchaseCode']);

    Route::post('/migrate-database', [InstallController::class, 'migrateDatabase']);

    Route::post('/complete', [InstallController::class, 'completeInstallation']);
});

CustomFormsRouter::publicRoutes();

Route::post('/login', [AccountController::class, 'login']);

Route::post('/register', [AccountController::class, 'register']);

Route::post('/forgot-password', [AccountController::class, 'forgotPassword']);

Route::post('/reset-password', [AccountController::class, 'resetPassword']);

Route::get('/qrcodes/preview', [QRCodeController::class, 'preview']);

Route::get('/captcha', [CaptchaController::class, 'getCaptcha']);

Route::get('/website-builder-storage/{type}', [GrapesJsController::class, 'load']);

Route::post('/website-builder-storage/{type}', [GrapesJsController::class, 'store']);

Route::get('/fonts', function () {
    $fonts = new GoogleFonts();

    return $fonts->listFamilies();
});

Route::post(
    '/account/resend-verification-email',
    [AccountController::class, 'resendVerificationEmail']
)->middleware(['auth:sanctum', 'throttle:6,1']);

Route::post('/account/is-email-found', [AccountController::class, 'isEmailFound']);

Route::post('/account/send-otp-code', [AccountController::class, 'sendOtpCode']);

Route::post('/account/verify-otp-code', [AccountController::class, 'verifyOtpCode']);

Route::post('/account/otp-registration', [AccountController::class, 'otpRegistration']);

Route::get('/myself', [AccountController::class, 'myself'])
    ->middleware(['auth:sanctum']);

Route::get(
    '/subscription-plans',
    [SubscriptionPlanController::class, 'index']
);

Route::get(
    '/subscription-plans/{subscriptionPlan}',
    [SubscriptionPlanController::class, 'show']
);

Route::get(
    '/files/{file:slug}/resource',
    [FilesController::class, 'resource']
);

Route::post(
    '/contacts',
    [
        ContactController::class,
        'store'
    ]
);

Route::get(
    '/payment-processors/{slug}',
    [PaymentProcessorController::class, 'view']
);


Route::get(
    '/translations/active',
    [TranslationController::class, 'activeTranslations']
);

Route::prefix('utils')->group(function () {
    Route::get('list-calling-codes', [UtilsController::class, 'listCallingCodes']);

    Route::get('my-calling-code', [UtilsController::class, 'myCallingCode']);
});

Route::get(
    '/bulk-operations/export-csv/{instance}',
    [BulkOperationsController::class, 'exportCsv']
);

Route::get(
    '/bulk-operations/{type}/csv-sample',
    [BulkOperationsController::class, 'csvSample']
);

Route::post(
    '/lead-form-response',
    [LeadFormResponseController::class, 'store']
);

Route::post(
    '/lead-form-response/check-fingerprint',
    [LeadFormResponseController::class, 'checkFingerprint']
);

Route::get(
    '/system/log-file',
    [SystemController::class, 'serveLogFile']
)->name('log-file');

Route::post(
    '/qrcode-types/{slug}',
    [QRCodeTypeController::class, 'apiCall']
);

Route::get('widgets/integration/{id}', [WidgetsController::class, 'integrationData']);

Route::middleware([
    'auth:sanctum',
    'verified',
    RemoveEmptyFieldsFromArrays::class,
    // 
])->group(function () {

    Route::get('/dashboard/super-admin', [DashboardController::class, 'getSuperUserDashboard']);

    Route::get('/dashboard/multi-qrcodes-stats', [DashboardController::class, 'getMultiQRCodesReport']);

    Route::post('/account/act-as/{user}', [AccountController::class, 'actAs']);

    Route::post('/account/generate-magic-login-url/{user}', [
        AccountController::class,
        'generateMagicLoginUrl'
    ]);

    Route::post(
        '/account/cancel-subscription',
        [AccountController::class, 'cancelSubscription']
    );

    Route::get(
        '/qrcodes/count/scans',
        [QRCodeController::class, 'getQRCodeScanCount']
    );

    Route::get(
        '/qrcodes/count',
        [QRCodeController::class, 'getQRCodeCount']
    );

    Route::crud(
        '/qrcodes',
        QRCodeController::class,
        QRCode::class
    );

    Route::post(
        '/qrcodes/{qrcode}/change-status',
        [QRCodeController::class, 'changeStatus']
    )
        ->can('update', 'qrcode');

    Route::post(
        '/qrcodes/{qrcode}/generate-table-qrcodes',
        [QRCodeController::class, 'generateTableQRCodes']
    )
        ->can('update', 'qrcode');

    Route::post(
        '/qrcodes/{qrcode}/reset',
        [QRCodeController::class, 'resetQRCode']
    )->can('reset', 'qrcode');

    Route::post(
        '/qrcodes/{qrcode}/change-type',
        [QRCodeController::class, 'changeType'],
    )->can('update', 'qrcode');

    Route::post(
        '/qrcodes/archive/{qrcode}',
        [QRCodeController::class, 'archive']
    )->can('archive,qrcode');

    Route::post(
        '/qrcodes/delete-all-recently-deleted',
        [QRCodeController::class, 'deleteAllRecentlyDeleted']
    );

    Route::post(
        '/qrcodes/{qrcode}/copy',
        [QRCodeController::class, 'copy']
    )->can('store,qrcode');

    Route::post(
        '/qrcodes/{qrcode}/logo',
        [QRCodeController::class, 'storeLogo']
    );

    Route::post(
        '/qrcodes/data-file',
        [QRCodeController::class, 'storeDataFile']
    );

    Route::post(
        '/qrcodes/{qrcode}/upload-design-file',
        [QRCodeController::class, 'uploadDesignFile']
    );

    Route::get(
        '/qrcodes/{qrcode}/compatible-svg',
        [QRCodeController::class, 'compatibleSVG']
    );

    Route::get(
        '/qrcodes/{qrcode}/business-review-feedbacks',
        [QRCodeController::class, 'getBusinessReviewFeedbacks']
    );

    Route::delete(
        '/qrcodes/{qrcode}/business-review-feedbacks/{feedback}',
        [QRCodeController::class, 'deleteBusinessReviewFeedback']
    );

    Route::post(
        '/qrcodes/{qrcode}/duplicate-biolink-block/{blockId}',
        [
            QRCodeController::class,
            'duplicateBioLinkBlock'
        ]
    );

    Route::get(
        '/qrcodes/{qrcode}/reports/{slug}',
        [QRCodeController::class, 'report']
    )->can('showStats,qrcode');

    Route::put(
        '/qrcodes/{qrcode}/redirect',
        [QRCodeController::class, 'updateRedirect']
    )->can('update', 'qrcode');

    Route::post(
        '/qrcodes/{qrcode}/set-allowed-scans',
        [QRCodeController::class, 'setAllowedScans']
    )
        ->can('update', 'qrcode');

    Route::get(
        '/qrcodes/{qrcode}/redirect',
        [QRCodeController::class, 'showRedirect']
    );

    Route::post(
        '/qrcode-templates',
        [QRCodeTemplatesController::class, 'saveTemplate']
    )->can('manage', QRCodeTemplate::class);

    Route::get(
        '/qrcode-templates/{qrcodeTemplate}',
        [QRCodeTemplatesController::class, 'getTemplate']
    )->can('manage', QRCodeTemplate::class);

    Route::get(
        '/qrcode-templates',
        [QRCodeTemplatesController::class, 'index']
    )->can('list', QRCodeTemplate::class);

    Route::post(
        '/qrcode-templates/{qrcodeTemplate}/use',
        [QRCodeTemplatesController::class, 'useTemplate']
    )->can('use', QRCodeTemplate::class);


    Route::post(
        '/qrcode-templates/{template}/use-in-existing',
        [QRCodeTemplatesController::class, 'useInExistingQRCode']
    );


    Route::get(
        QRCodeStorage::SERVE_QRCODE_SVG_FILE_ROUTE,
        [QRCodeController::class, 'serveSvgFile']
    );

    Route::get(
        '/bulk-operations/types',
        [BulkOperationsController::class, 'listOperationTypes']
    );

    Route::post(
        '/bulk-operations/{type}/create',
        [BulkOperationsController::class, 'storeOperation']
    );

    Route::post(
        '/bulk-operations/{instance}/re-run',
        [BulkOperationsController::class, 'reRun']
    );

    Route::get(
        '/bulk-operations/{type}/instances',
        [BulkOperationsController::class, 'listInstances']
    );

    Route::get(
        '/bulk-operations/instance-results/{instance}',
        [BulkOperationsController::class, 'listInstanceResults']
    );

    Route::post(
        '/bulk-operations/edit-instance-name/{instance}',
        [BulkOperationsController::class, 'editInstanceName']
    );

    Route::delete(
        '/bulk-operations/{instance}',
        [BulkOperationsController::class, 'deleteInstance']
    );

    Route::delete(
        '/bulk-operations/{instance}/all-qrcodes',
        [BulkOperationsController::class, 'deleteAllQRCodesOfInstance']
    );

    Route::post(
        '/bulk-operations/process-cut-contour/{file}',
        [BulkOperationsController::class, 'processCutContour']
    );

    Route::post(
        '/qrcodes/{qrcode}/background-image',
        [QRCodeController::class, 'storeForegroundImage']
    );

    Route::get(
        '/qrcodes/{qrcode}/webpage-design',
        [QRCodeController::class, 'getWebPageDesign']
    );

    Route::post(
        '/qrcodes/{qrcode}/webpage-design',
        [QRCodeController::class, 'saveWebPageDesign']
    )->can('update', 'qrcode');

    Route::post(
        '/qrcodes/{qrcode}/webpage-design-file',
        [QRCodeController::class, 'storeWebPageDesignFile']
    );

    Route::post(
        '/qrcodes/{qrcode}/change-user',
        [QRCodeController::class, 'changeQRCodeUser']
    )->can('changeUser', QRCode::class);

    Route::post(
        '/qrcodes/{qrcode}/pincode',
        [QRCodeController::class, 'setPincode']
    )->can('setPincode', 'qrcode');

    Route::post(
        '/users/{user}/invite-sub-user',
        [UsersController::class, 'inviteSubUser']
    )->can('inviteSubUser', 'user');

    Route::get(
        '/users/{user}/sub-users',
        [UsersController::class, 'listSubUsers']
    )->can('listSubUsers', 'user');

    Route::post(
        '/users/{user}/reset-role',
        [UsersController::class, 'resetRole']
    );

    Route::post(
        '/users/{user}/reset-scans-limit',
        [UsersController::class, 'resetScansLimit']
    );

    Route::delete(
        '/users/{user}/sub-users/{subUser}',
        [UsersController::class, 'deleteSubUser']
    )->can('deleteSubUser', ['user', 'subUser']);

    Route::post(
        '/users/{user}/change-account-balance',
        [UsersController::class, 'changeAccountBalance']
    )->can('changeAccountBalance', 'user');

    Route::get(
        '/users/{user}/account-balance',
        [UsersController::class, 'getAccountBalance']
    )->can('getAccountBalance', 'user');

    Route::crud(
        '/users',
        UsersController::class,
        User::class
    );

    Route::post(
        '/users/verify-email/{user}',
        [UsersController::class, 'verifyEmail']
    )->can('forceVerifyEmail', 'user');

    Route::crud(
        '/subscription-plans',
        SubscriptionPlanController::class,
        SubscriptionPlan::class,
        except: ['index', 'show']
    );



    Route::post(
        '/subscription-plans/{subscriptionPlan}/duplicate',
        [
            SubscriptionPlanController::class,
            'duplicate'
        ]
    )->can('duplicate', 'subscriptionPlan');

    Route::get(
        '/payment-processors',
        [PaymentProcessorController::class, 'index']
    );

    Route::post(
        '/payment-processors/{processorSlug}/generate-pay-link/{plan}',
        [PaymentProcessorController::class, 'generatePayLink']
    );

    Route::post(
        '/payment-processors/{processorSlug}/create-charge-link/{amount}',
        [PaymentProcessorController::class, 'createChargeLink']
    );

    Route::post(
        '/payment-processors/{processorSlug}/forward/{method}',
        [PaymentProcessorController::class, 'forwardCall']
    );

    Route::post(
        '/payment-processors/{processorSlug}/test-credentials',
        [PaymentProcessorController::class, 'testCredentials']
    );

    Route::post(
        '/payment-processors/{processorSlug}/register-webhook',
        [PaymentProcessorController::class, 'registerWebhook']
    );

    Route::get(
        '/subscriptions/statuses',
        [SubscriptionController::class, 'listStatuses']
    );

    Route::crud(
        '/subscriptions',
        SubscriptionController::class,
        Subscription::class,
        only: ['index', 'show', 'store', 'update']
    );

    Route::post(
        '/subscriptions/delete-pending',
        [SubscriptionController::class, 'deletePendingSubscriptions']
    );


    Route::get(
        '/transactions',
        [TransactionController::class, 'index']
    )->can('list,App\Models\Transaction');

    Route::post(
        '/transactions/upload-proof-of-payment',
        [TransactionController::class, 'uploadProofOfPayment']
    );

    Route::post(
        '/transactions/offline-transaction',
        [TransactionController::class, 'storeOfflineTransaction']
    );

    Route::post(
        '/transactions/{transaction}/approve',
        [TransactionController::class, 'approveOfflineTransaction']
    )->can('approve,transaction');

    Route::post(
        '/transactions/{transaction}/reject',
        [TransactionController::class, 'rejectOfflineTransaction']
    )->can('reject,transaction');


    Route::get(
        '/qrcode-scans/count',
        [QRCodeScanController::class, 'count']
    );

    Route::crud(
        '/files',
        FilesController::class,
        File::class,
        only: ['show', 'store', 'destroy']
    );

    Route::post(
        'files/chunk-upload',
        [FilesController::class, 'chunkUpload']
    );

    Route::post(
        'files/merge',
        [FilesController::class, 'chunksMerge']
    );

    Route::crud(
        '/blog-posts',
        BlogPostController::class,
        BlogPost::class
    );

    Route::post(
        '/blog-posts/{post}/upload-featured-image',
        [BlogPostController::class, 'uploadFeaturedImage']
    );

    Route::post(
        '/markdown',
        MarkdownController::class
    );

    Route::crud(
        '/content-blocks',
        ContentBlockController::class,
        ContentBlock::class
    );

    Route::delete(
        '/content-blocks/of-translation/{translationId}',
        [ContentBlockController::class, 'destroyAllOfTranslation']
    )->can('deleteAllBlocks', ContentBlock::class);;

    Route::post(
        '/content-blocks/copy/from/{sourceTranslation}/to/{destinationTranslation}',
        [ContentBlockController::class, 'copyContentBlocks']
    )->can('copyAllBlocks', ContentBlock::class);

    Route::crud(
        '/contacts',
        ContactController::class,
        Contact::class,
        except: ['store']
    );

    Route::crud(
        '/widgets',
        WidgetsController::class,
        Widget::class,
    );



    Route::get(
        '/system/status',
        [SystemController::class, 'status']
    )->can('system.status');

    Route::get(
        '/system/logs',
        [SystemController::class, 'serveLogs']
    )->can('system.logs');

    Route::post(
        '/system/log-file',
        [SystemController::class, 'generateLogFileURL']
    )->can('system.logs');

    Route::delete(
        '/system/log-file',
        [SystemController::class, 'clearLogFile']
    )->can('system.logs');

    Route::post(
        '/system/test-smtp',
        [SystemController::class, 'testSmtp'],
    )->can('system.settings');

    Route::post(
        '/system/rebuild-cache/{type}',
        [SystemController::class, 'rebuildCache']
    )->can('system.cache');

    Route::post(
        '/system/clear-cache/{type}',
        [SystemController::class, 'clearCache']
    )->can('system.cache');

    Route::get(
        '/system/check_database_update',
        [SystemController::class, 'checkDatabaseUpdate']
    )->can('system.status');

    Route::post(
        '/system/update_database',
        [SystemController::class, 'updateDatabase']
    )->can('system.status');

    Route::get(
        '/system/configs',
        [SystemController::class, 'getConfigs']
    );

    Route::post(
        '/system/configs',
        [SystemController::class, 'saveConfigs']
    );

    Route::post(
        '/system/configs/upload',
        [SystemController::class, 'uploadConfigAttachment']
    );

    Route::get(
        '/system/timezones',
        [SystemController::class, 'getTimezones']
    );

    Route::post(
        '/system/test-storage',
        [SystemController::class, 'testStorage']
    )->can('system.status');

    Route::get(
        '/system/latest-version',
        [SystemController::class, 'getLatestVersion'],
    )->can('system.status');

    Route::post(
        '/system/self-update',
        [SystemController::class, 'updateSelf']
    )->can('system.status');

    Route::get(
        '/translations/can-auto-translate',
        [TranslationController::class, 'canAutoTranslate']
    );


    Route::post(
        '/translations/lines',
        [TranslationController::class, 'saveLine']
    );

    Route::get(
        '/translations/lines',
        [TranslationController::class, 'getLines']
    );

    Route::post(
        '/translations/config-lines',
        [TranslationController::class, 'saveConfigLine']
    );

    Route::get(
        '/translations/config-lines',
        [TranslationController::class, 'getConfigLines']
    );

    Route::crud(
        '/translations',
        TranslationController::class,
        Translation::class,
    );


    Route::post(
        '/translations/{translation}/upload',
        [TranslationController::class, 'upload']
    );

    Route::post(
        '/translations/{translation}/toggle-activate',
        [TranslationController::class, 'toggleActivate']
    );

    Route::post(
        '/translations/{translation}/set-main',
        [TranslationController::class, 'setMain']
    );

    Route::post(
        '/translations/{translation}/auto-translate',
        [TranslationController::class, 'autoTranslate']
    );

    Route::post(
        '/checkout/stripe/{subscription}',
        [CheckoutController::class, 'stripeCheckout']
    );

    Route::post(
        '/checkout/stripe/verify-checkout-session/{sessionId}',
        [CheckoutController::class, 'stripeVerifyCheckoutSession']
    );

    Route::crud(
        '/roles',
        RolesController::class,
        Role::class
    );

    Route::get(
        '/permissions',
        [PermissionsController::class, 'index']
    );

    Route::crud(
        '/currencies',
        CurrencyController::class,
        Currency::class
    );

    Route::post(
        '/currencies/{currency}/enable',
        [CurrencyController::class, 'enableCurrency']
    )->can('enable,currency');

    Route::get(
        '/custom-codes/positions',
        [CustomCodeController::class, 'getPositions']
    );

    Route::crud(
        '/custom-codes',
        CustomCodeController::class,
        CustomCode::class
    );

    Route::crud(
        '/pages',
        PageController::class,
        Page::class
    );

    Route::get(
        '/domains/usable',
        [DomainController::class, 'usableDomains'],
    );

    Route::get(
        '/domains/my-domains',
        [DomainController::class, 'myDomains']
    );

    Route::crud(
        '/domains',
        DomainController::class,
        Domain::class
    );

    Route::get(
        '/domains/{domain}/check-connectivity',
        [DomainController::class, 'checkConnectivity']
    );

    Route::put(
        '/domains/{domain}/update-status',
        [DomainController::class, 'updateStatus']
    );

    Route::put(
        '/domains/{domain}/update-availability',
        [DomainController::class, 'updateAvailability']
    );

    Route::put(
        '/domains/{domain}/set-default',
        [DomainController::class, 'setDefaultDomain']
    )->can('setDefault,domain');


    Route::get('/folders/{user}', [
        FolderController::class,
        'index'
    ]);

    Route::get('/folders/{user}/{folder}', [
        FolderController::class,
        'show',
    ]);

    Route::post('/folders/{user}', [
        FolderController::class,
        'store',
    ]);

    Route::put('/folders/{user}/{folder}', [
        FolderController::class,
        'store',
    ]);

    Route::delete('/folders/{user}/{folder}', [
        FolderController::class,
        'destroy',
    ]);

    Route::post(
        '/dynamic-biolink-blocks/store-file',
        [DynamicBioLinkBlockController::class, 'storeFile']
    )->can('store', DynamicBioLinkBlock::class);

    Route::crud(
        '/dynamic-biolink-blocks',
        DynamicBioLinkBlockController::class,
        DynamicBioLinkBlock::class,
        paramName: 'dynamicBioLinkBlock',
        except: ['index']
    );

    Route::get(
        '/dynamic-biolink-blocks',
        [DynamicBioLinkBlockController::class, 'index']
    );

    Route::get(
        '/lead-forms',
        [LeadFormController::class, 'index']
    )->can('list', 'App\\Models\\LeadForm');

    Route::get(
        '/lead-forms/{leadForm}',
        [LeadFormController::class, 'show']
    )->can('show', 'leadForm');

    Route::post(
        '/lead-forms',
        [LeadFormController::class, 'store']
    )->can('store', 'App\\Models\\LeadForm');

    Route::put(
        '/lead-forms/{leadForm}',
        [LeadFormController::class, 'update']
    )->can('update', 'leadForm');

    Route::get(
        '/lead-forms/{leadForm}/responses',
        [
            LeadFormResponseController::class,
            'ofLeadForm'
        ]
    )->can('show', 'leadForm');

    Route::delete(
        '/lead-form-responses/{response}',
        [LeadFormResponseController::class, 'destroy']
    );

    Route::post(
        '/ai/generate/{qrcode}',
        [AiGeneratorController::class, 'generate']
    )->can('update', 'qrcode');

    Route::get(
        '/ai/fetch/{qrcode}',
        [AiGeneratorController::class, 'fetchPrediction']
    )->can('show', 'qrcode');

    Route::get(
        '/plugins/installed',
        [PluginsController::class, 'listInstalledPlugins']
    )->can('plugins.manage');

    Route::get(
        '/plugins/plugin/{slug}',
        [PluginsController::class, 'viewPlugin']
    );

    Route::post(
        '/generate-website-builder-url/{qrcode}',
        [GrapesJsController::class, 'generateWebsiteBuilderUrl']
    );

    CustomFormsRouter::protectedRoutes();

    Route::prefix('billing-collection')->group(function () {

        Route::get('is-enabled', [
            BillingCollectionController::class,
            'isBillingCollectionEnabled'
        ]);

        Route::get('form/{formSlug}', [
            BillingCollectionController::class,
            'getForm'
        ]);
        // 
        Route::get(
            'latest-billing-form-response',
            [BillingCollectionController::class, 'getLatestBillingFormResponseId']
        );
    });

    Route::crud(
        '/template-categories',
        TemplateCategoryController::class,
        TemplateCategory::class
    );

    Route::get('invoices', [InvoiceController::class, 'index']);
});
