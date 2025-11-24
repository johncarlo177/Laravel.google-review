<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Models\QRCode;
use Illuminate\Http\Request;
use App\Models\QRCodeRedirect;
use App\Plugins\PluginManager;
use App\Interfaces\DeviceInfo;
use App\Support\QRCodeScanManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Interfaces\SubscriptionManager;
use App\Support\QRCodeErrorPage;
use App\Support\System\Traits\WriteLogs;
use App\Support\QRCodeTypes\BaseDynamicType;
use App\Support\QRCodeTypes\Interfaces\ShouldImmediatlyRedirectToDestination;
use App\Support\Webhooks\QRCodeScanDispatcher;

class QRCodeRedirectController extends Controller
{
    use WriteLogs;

    private SubscriptionManager $subscriptionManager;

    private QRCodeScanManager $scanManager;

    public const SCAN_PREFIX = ['scan', 's'];

    public static function bindRoutes()
    {
        Route::get('/scan/{slug}', [static::class, 'index']);

        Route::get('/s/{slug}', [static::class, 'index']);

        Route::post('/s/{slug}', [static::class, 'index']);
    }

    public static function serveQRCode(QRCode $qrcode)
    {
        $redirect = $qrcode->redirect;

        return static::serveSlug($redirect->slug);
    }

    public static function serveSlug(string $slug)
    {
        return app()->call(static::class . '@index', [
            'slug' => $slug,
            'request' => request(),
        ]);
    }

    public function __construct()
    {
        $this->subscriptionManager = app(SubscriptionManager::class);
        $this->scanManager = app(QRCodeScanManager::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        Request $request,
        DeviceInfo $info,
        $slug,
    ) {
        $redirect = QRCodeRedirect::whereSlug($slug)->first();

        if (!$redirect) {

            abort(404);
        }

        $user = $redirect->qrcode->user;

        if (!$this->isAccessAllowedBySubscriptionRules($user, $redirect->qrcode)) {
            return $this->renderSubscriptionError($user, $redirect->qrcode);
        }

        if ($redirect->qrcode->archived) {
            return $this->renderArchivedPage();
        }

        if ($redirect->qrcode->status === QRCode::STATUS_DISABLED) {
            return $this->renderDisabledPage();
        }

        if ($this->didReachAllowedQRCodeScanLimit($redirect)) {
            return $this->renderAllowedScansLimitReachedError();
        }

        if (!$info->isBot()) {
            $this->collectScanDetails($redirect, $request);
        }


        return $this->renderRedirect($redirect);
    }

    protected function didReachAllowedQRCodeScanLimit($redirect)
    {
        $configValue = config('qrcode.allowed_scans_limit_enforcement');

        if ($configValue === 'disabled') {
            return false;
        }

        if (!$redirect->qrcode->allowed_scans) {
            return false;
        }

        return $redirect->qrcode->scans_count >= $redirect->qrcode->allowed_scans;
    }

    protected function renderAllowedScansLimitReachedError()
    {
        return QRCodeErrorPage::withTitle(
            t('Scans Limit Reached')
        )->withContent(
            t('This QR Code cannot be scanned, we cannot show its content right now. <br>  If you are the owner of this QR code you may re-enable it from your dashboard account.')
        )
            ->withType(QRCodeErrorPage::TYPE_ALLOWED_SCANS_LIMIT_REACHED)
            ->render();
    }

    protected function renderArchivedPage()
    {
        return view('blue.pages.qrcode-error', [
            'title' => t('Archived QR Code'),
            'meta_description' => t('Archived QR Code'),
            'content' => t('This QR Code is archived, please visit this page later. <br>  If you are the owner of this QR code you may restore it from Archived QR codes menu item.')
        ]);
    }

    protected function renderDisabledPage()
    {
        return QRCodeErrorPage::withTitle(
            t('Disabled QR Code')
        )->withContent(
            t('This QR Code is disabled, we cannot show its content right now. <br>  If you are the owner of this QR code you may re-enable it from your dashboard account.')
        )
            ->withType(QRCodeErrorPage::TYPE_DISABLED)
            ->render();
    }



    private function collectScanDetails(
        QRCodeRedirect $redirect,
        Request $request
    ) {
        if ($request->boolean('preview')) return;

        try {
            $scan = $this->scanManager->collectScanDetails(
                $redirect->qrcode,
                $request
            );

            $scan->qrcode_redirect_id = $redirect->id;

            $scan->save();

            QRCodeScanDispatcher::withScan($scan)->dispatch();
            // 
        } catch (Throwable $th) {
            Log::warning(sprintf(
                'Could not save scan details for user agent %s, %s',
                @$_SERVER['HTTP_USER_AGENT'],
                $th->getMessage()
            ));
        }
    }

    private function renderRedirect(QRCodeRedirect $redirect)
    {
        $type = $redirect->qrcode->resolveType();

        $qrcode = $redirect->qrcode;

        if (!($type instanceof BaseDynamicType)) {
            return view('blue.pages.qrcode-error', [
                'title' => t('Static QR Code'),
                'meta_description' => t('Static QR Code.'),
                'content' => t(
                    'The type of this QR code has been changed, we cannot view its content. <br>  If you are the owner of this QR code you may change it back to a dynamic type. Current type is'
                ) . sprintf(' (%s) ', $type::slug())
            ]);
        }

        try {
            return $type->renderView($qrcode);
        } catch (Throwable $th) {


            Log::warning(sprintf('QRCode render error: %s', $th->getMessage()));

            return view('blue.pages.qrcode-error', [
                'title' => t('Server Error'),
                'meta_description' => t('Server Error.'),
                'content' => t(
                    'We cannot view the content of this QR code now, please visit this page later. <br>  If you are the owner of this QR code please contact our support team.'
                )
            ]);
        }
    }

    protected function isAccessAllowedBySubscriptionRules(User $user, QRCode $qrcode)
    {
        $shouldApplyRules = PluginManager::doFilter(
            PluginManager::FILTER_SHOULD_APPLY_QRCODE_SUBSCRIPTION_RULES,
            $this
                ->subscriptionManager
                ->shouldEnforceSubscriptionRules($user),
            $qrcode
        );

        if (!$shouldApplyRules) {
            return true;
        }

        if (!$this->subscriptionManager->userHasActiveSubscription($user)) {
            return false;
        }

        if ($this->subscriptionManager->userScanLimitReached($user)) {
            return false;
        }

        return true;
    }

    private function renderSubscriptionError(User $user, QRCode $qrcode)
    {
        if (!$this->subscriptionManager->userHasActiveSubscription($user)) {
            return view('blue.pages.qrcode-error', [
                'title' => t('Subscription Expired'),
                'meta_description' => t('Subscription expired.'),
                'content' => t('This QR Code is not available right now, please visit this page later. <br>  If you are the owner of this QR code you renew your subscription to re activate this QR Code.')
            ]);
        }

        if ($this->subscriptionManager->userScanLimitReached($user)) {
            return view('blue.pages.qrcode-error', [
                'title' => t('Scan Limit Reached'),
                'meta_description' => t('Scan limit reached.'),
                'content' => t('This QR Code is not available right now, please visit this page later. <br>  If you are the owner of this QR code you may upgrade the your subscription plan.')
            ]);
        }
    }
}
