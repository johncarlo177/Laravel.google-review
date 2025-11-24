<?php

namespace App\Http\Controllers;

use App\Events\ShouldSaveQRCodeVariants;
use App\Http\Middleware\ErrorMessageMiddleware;
use App\Http\Requests\QRCodeRequest;

use App\Http\Requests\ArchiveQRCodeRequest;
use App\Interfaces\FileManager;
use App\Models\QRCode;

use Illuminate\Http\Request;

use App\Interfaces\QRCodeGenerator;
use App\Interfaces\SubscriptionManager;
use App\Models\BusinessReviewFeedback;
use App\Models\User;
use App\Plugins\PluginManager;
use App\Policies\Restriction\QRCodeRestrictor;
use App\Support\Apple\ApplePassGenerator;
use App\Support\Billing\AccountCreditBillingManager;
use App\Support\Billing\BillingManager;
use App\Support\CompatibleSVG\CompatibleSVGManager;
use App\Support\FolderManager;
use App\Support\QRCodeManager;
use App\Support\QRCodeRedirectManager;
use App\Support\QRCodeReports\ReportsManager;
use App\Support\QRCodeScanManager;
use App\Support\QRCodeSearchBuilder;
use App\Support\QRCodeStorage;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\QRCodeTypes\ViewComposers\BioLinks;
use App\Support\QRCodeTypes\ViewComposers\Cache\QRCodeCacheManager;
use App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon\FileServer;
use App\Support\QRCodeTypes\ViewComposers\UpiDynamic;
use App\Support\QRCodeWebPageDesignManager;
use App\Support\System\Traits\WriteLogs;
use Throwable;

class QRCodeController extends Controller
{
    use WriteLogs;

    private QRCodeGenerator $generator;
    private QRCodeManager $qrcodeManager;
    private QRCodeWebPageDesignManager $webpageDesignManager;
    private QRCodeTypeManager $typeManager;
    private FolderManager $folders;
    private QRCodeScanManager $scans;

    private BillingManager $billingManager;

    private AccountCreditBillingManager $accountCredit;
    private SubscriptionManager $subscriptions;

    public function __construct(
        QRCodeGenerator $generator,
        QRCodeManager $qrcodeManager,
        QRCodeWebPageDesignManager $webpageDesignManager,
        QRCodeTypeManager $typeManager,
        FolderManager $folders,
        QRCodeScanManager $scans,
        BillingManager $billingManager,
        AccountCreditBillingManager $accountCredit,
        SubscriptionManager $subscriptions,
    ) {
        $this->generator = $generator;
        $this->qrcodeManager = $qrcodeManager;
        $this->webpageDesignManager = $webpageDesignManager;
        $this->typeManager = $typeManager;
        $this->folders = $folders;
        $this->scans = $scans;
        $this->billingManager = $billingManager;
        $this->accountCredit = $accountCredit;
        $this->subscriptions = $subscriptions;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->boolean('list_all')) {
            if (!$request->user()->isSuperAdmin()) {
                return [];
            }

            return QRCode::orderBy('id', 'desc')->select('id', 'name')->get();
        }

        $this->logDebug('Request type = %s', request()->input('type'));

        return (new QRCodeSearchBuilder)
            ->byUser($request->user())
            ->with('redirect')
            ->withPageSize($request->input('page_size', 10))
            ->withSort($request->input('sort'))
            ->forQrCodesCreatedBy($request->input('user_id'))
            ->archived($request->boolean('search_archived'))
            ->withKeyword($request->keyword)
            ->type($request->input('type'))
            ->scansRange($request->scans_count)
            ->folder($request->folder_id)
            ->applyClientRestrictions()
            ->applySubUserRestrictions()
            ->withoutPagination($request->boolean('no_pagination'))
            ->sort()
            ->paginationPath($request->input('path'))
            ->paginate();
    }

    public function show(QRCode $qrcode)
    {
        return $qrcode;
    }

    public function showRedirect(QRCode $qrcode)
    {
        return $qrcode->redirect;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\QRCodeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(QRCodeRequest $request)
    {
        $this->typeManager->find(
            $request->input('type')
        )->validate($request);

        $qrcode = new QRCode($request->all());

        $qrcode->user_id = $request->user()->id;

        if ($request->user()->is_sub) {
            $qrcode->folder_id = $this->folders->getSubuserFolders($request->user())->get(0)->id;
        }

        $qrcode->save();

        if ($this->billingManager->isAccountCreditBilling()) {
            $this
                ->accountCredit
                ->forUser(request()->user())
                ->deductQRCodePrice($qrcode);
        }

        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $qrcode;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\QRCodeRequest  $request
     * @param  \App\Models\QRCode  $qrcode
     * @return \Illuminate\Http\Response
     */
    public function update(QRCodeRequest $request, QRCode $qrcode)
    {
        $this->typeManager->find(
            $request->input('type')
        )->validate($request);

        $request = PluginManager::doFilter(
            PluginManager::FILTER_QRCODE_UPDATE_REQUEST,
            $request,
            $qrcode
        );

        $qrcode->fill($request->all())->save();

        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $qrcode;
    }

    public function changeStatus(Request $request, QRCode $qrcode)
    {
        $qrcode->status = $request->input('status');

        $qrcode->save();

        return $qrcode;
    }

    public function updateRedirect(Request $request, QRCode $qrcode)
    {
        QRCodeRestrictor::make($qrcode->id)->applyRestrictions();

        $redirect = $qrcode->redirect;

        if (!$redirect) {
            abort(404);
        }

        $qrcodeRedirectManager = new QRCodeRedirectManager();

        $result = $qrcodeRedirectManager
            ->updateRedirect($redirect, $request->all());

        event(new ShouldSaveQRCodeVariants($qrcode));

        $qrcode->touch();

        return $result;
    }

    public function archive(ArchiveQRCodeRequest $request, QRCode $qrcode)
    {
        QRCodeRestrictor::make($qrcode->id)->applyRestrictions();

        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $this->qrcodeManager->archive($qrcode, $request->boolean('archived'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QRCode  $qrcode
     * @return \Illuminate\Http\Response
     */
    public function destroy(QRCode $qrcode, QRCodeManager $qrcodeManager)
    {
        $qrcodeManager->delete($qrcode);

        return $qrcode;
    }

    public function storeLogo(Request $request, QRCode $qrcode, FileManager $files)
    {
        $request->merge([
            'attachable_type' => QRCode::class,
            'attachable_id' => $qrcode->id,
            'type' => FileManager::FILE_TYPE_QRCODE_LOGO
        ]);

        $result = $files->store($request);

        $qrcode->refresh();

        event(new ShouldSaveQRCodeVariants($qrcode));

        $qrcode->touch();

        return $result;
    }

    public function uploadDesignFile(Request $request, QRCode $qrcode, FileManager $files)
    {
        $request->merge([
            'attachable_type' => QRCode::class,
            'attachable_id' => $qrcode->id,
            'type' => FileManager::FILE_TYPE_QRCODE_DESIGN_FILE
        ]);

        $name = $request->name && $request->name != 'undefined' ? $request->name : null;

        $result = $files->store($request);

        if ($name) {
            $merged = array_merge(
                (array) $qrcode->design,
                [
                    $request->name => $result->id
                ]
            );

            $qrcode->design = $merged;

            $qrcode->save();
        }

        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $result;
    }

    public function storeForegroundImage(Request $request, QRCode $qrcode, FileManager $files)
    {
        $request->merge([
            'attachable_type' => QRCode::class,
            'attachable_id' => $qrcode->id,
            'type' => FileManager::FILE_TYPE_QRCODE_FOREGROUND_IMAGE
        ]);

        $result = $files->store($request);

        event(new ShouldSaveQRCodeVariants($qrcode));

        $qrcode->touch();

        return $result;
    }

    public function preview(Request $request)
    {
        try {
            $this->generator->initFromRequest($request);

            return $this->generator->respondInline();
        } catch (Throwable $th) {

            $this->logDebugf($th->getMessage());
            $this->logDebug($th->getTraceAsString());

            return 'Invalid parameters';
        }
    }

    public function report(Request $request, QRCode $qrcode, $slug)
    {
        return ReportsManager::report($slug)
            ->of($qrcode)
            ->from($request->from)
            ->to($request->to)
            ->generate();
    }

    public function copy(QRCode $qrcode, Request $request)
    {
        $count = $request->input('count');

        if (app()->environment('demo')) {
            $count = 1;
        }

        if (
            !$this
                ->subscriptions
                ->allowedToCreateDynamicQRCodes(
                    $request->user(),
                    $count,
                    $qrcode->type
                )
        ) {
            ErrorMessageMiddleware::abortWithMessage(
                t('QR Code Limit Reached'),
                422
            );
        }

        return $this->qrcodeManager->copy($qrcode, $count);
    }

    public function getQRCodeScanCount(Request $request)
    {
        return [
            'count' => $this->scans->getScansByUser($request->user())
        ];
    }

    public function getQRCodeCount(Request $request)
    {
        $types = [];

        if (!empty($request->qrcode_type))
            $types = explode(',', $request->qrcode_type);

        return [
            'count' => $this->qrcodeManager->getQRCodeCount(
                actor: $request->user(),
                qrcodeMaker: User::find($request->user_id),
                qrcodeType: $types
            )
        ];
    }

    public function getWebPageDesign(QRCode $qrcode)
    {
        return $this->webpageDesignManager->getDesignOrCreateNewDesignIfNeeded($qrcode);
    }

    public function saveWebPageDesign(QRCode $qrcode, Request $request)
    {
        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $this->webpageDesignManager->saveDesign(
            $qrcode,
            $request->all()
        );
    }

    public function storeWebPageDesignFile(Request $request, QRCode $qrcode, FileManager $files)
    {
        $design = $this->webpageDesignManager->saveDesign($qrcode, []);

        $request->merge([
            'attachable_type' => $design::class,
            'attachable_id' => $design->id,
            'type' => FileManager::FILE_TYPE_QRCODE_WEBPAGEDESIGN
        ]);

        $result = $files->store($request);

        dispatch(function () use ($qrcode) {
            QRCodeCacheManager::withQRCode($qrcode)->clear();
        })->afterResponse();

        return $result;
    }

    public function changeQRCodeUser(QRCode $qrcode, Request $request)
    {
        return $this->qrcodeManager->changeUser(
            $qrcode,
            $request->input('user_id')
        );
    }

    /**
     * 
     */
    public function setPincode(QRCode $qrcode, Request $request)
    {
        if (!$this->optionalUser()->permitted('qrcode.pin-code')) {
            return;
        }

        return $this->qrcodeManager->setPincode($qrcode, $request->input('pincode'));
    }

    public function compatibleSVG(QRCode $qrcode)
    {
        $manager = new CompatibleSVGManager($qrcode);

        return [
            'svg' => $manager->render()
        ];
    }

    public function serveSvgFile(QRCode $qrcode)
    {
        return QRCodeStorage::ofQRCode($qrcode)->serveSvgFile();
    }

    public function serveFavicon(QRCode $qrcode, $fileName)
    {
        $favicon = new FileServer($qrcode);

        return $favicon->serve($fileName);
    }

    public function storeDataFile(
        Request $request,
        FileManager $files
    ) {
        $request->merge([
            'attachable_type' => QRCode::class,
            'attachable_id' => null,
            'type' => FileManager::FILE_TYPE_GENERAL_USE_FILE
        ]);

        $result = $files->store($request);

        return $result;
    }

    public function changeType(QRCode $qrcode)
    {
        try {
            $request = PluginManager::doFilter(
                PluginManager::FILTER_QRCODE_UPDATE_REQUEST,
                request(),
                $qrcode
            );

            dispatch(function () use ($qrcode) {
                QRCodeCacheManager::withQRCode($qrcode)->clear();
            })->afterResponse();

            return $this->qrcodeManager->changeType(
                $qrcode,
                $request->input('type')
            );
        } catch (Throwable $th) {
            $this->logWarning('Error while changing QR code type. %s %s', $th->getMessage(), $th->getTraceAsString());

            abort(422, t('Invalid type'));
        }
    }

    protected function authorizeOwnership(QRCode $qrcode)
    {
        if (!$this->optionalUser()) {
            abort(401);
        }

        if ($this->optionalUser()->isSuperAdmin()) {
            return;
        }

        if ($qrcode->user_id != $this->optionalUser()->id) {
            abort(403);
        }
    }

    public function getBusinessReviewFeedbacks(QRCode $qrcode)
    {
        $this->authorizeOwnership($qrcode);

        return BusinessReviewFeedback::where(
            'qrcode_id',
            $qrcode->id
        )->get();
    }

    // NEW METHOD: List all feedbacks for dashboard page 📨
    public function indexFeedbacks(Request $request)
    {
        $user = $this->optionalUser();
        
        // Get all QR codes owned by the user
        $qrcodeIds = QRCode::where('user_id', $user->id)
            ->pluck('id');

        // Build query for feedbacks
        $query = BusinessReviewFeedback::whereIn('qrcode_id', $qrcodeIds)
            ->with('qrcode:id,title,short_url');

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('feedback', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Apply QR code filter
        if ($request->has('qrcode') && $request->qrcode !== 'all') {
            $query->where('qrcode_id', $request->qrcode);
        }

        // Apply stars filter
        if ($request->has('stars') && $request->stars !== 'all') {
            $query->where('stars', $request->stars);
        }

        // Apply sorting
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $feedbacks = $query->paginate($perPage)->withQueryString();

        // Calculate summary stats
        $allFeedbacks = BusinessReviewFeedback::whereIn('qrcode_id', $qrcodeIds)->get();
        $summary = [
            'total' => $allFeedbacks->count(),
            'stars_5' => $allFeedbacks->where('stars', 5)->count(),
            'stars_4' => $allFeedbacks->where('stars', 4)->count(),
            'stars_3' => $allFeedbacks->where('stars', 3)->count(),
            'stars_1_2' => $allFeedbacks->whereIn('stars', [1, 2])->count(),
        ];

        // Get all QR codes for filter dropdown
        $qrcodes = QRCode::where('user_id', $user->id)
            ->where('type', 'business-review')
            ->select('id', 'title')
            ->get();

        return inertia('feedbacks/index', [
            'feedbacks' => [
                'data' => $feedbacks->items(),
                'from' => $feedbacks->firstItem(),
                'to' => $feedbacks->lastItem(),
                'total' => $feedbacks->total(),
                'links' => $feedbacks->linkCollection()->toArray(),
                'summary' => $summary,
            ],
            'qrcodes' => $qrcodes,
            'filters' => $request->only(['search', 'qrcode', 'stars', 'sort_field', 'sort_direction', 'per_page']),
        ]);
    }

    public function deleteBusinessReviewFeedback(
        QRCode $qrcode,
        BusinessReviewFeedback $feedback
    ) {
        $this->authorizeOwnership($qrcode);

        return BusinessReviewFeedback::where(
            'qrcode_id',
            $qrcode->id
        )
            ->where('id', $feedback->id)
            ->limit(1)
            ->delete();
    }

    public function addToAppleWallet(QRCode $qrcode)
    {
        try {
            return response(
                ApplePassGenerator::withQRCode($qrcode)
                    ->generate()
                    ->create()
            )->header('Content-Type', 'application/vnd.apple.pkpass');
            // 
        } catch (Throwable $th) {

            $this->logWarning($th->getMessage());

            $this->logWarning($th->getTraceAsString());

            return redirect()->back();
        }
    }

    public function duplicateBioLinkBlock(QRCode $qrcode, $blockId)
    {
        $newBlock = BioLinks::withQRCode($qrcode)
            ->duplicateBlock(
                $blockId
            );

        return $newBlock->toArray();
    }

    public function resetQRCode(QRCode $qrcode)
    {
        (new QRCodeScanManager)->resetQRCodeScans($qrcode);
    }

    public function setAllowedScans(QRCode $qrcode)
    {
        $qrcode->allowed_scans = request()->input('allowed_scans');

        $qrcode->save();

        return $qrcode;
    }

    public function upiRedirect(QRCode $qrcode)
    {
        if ($qrcode->type !== 'upi-dynamic') {
            abort(422, 'Invalid QR Code');
        }

        UpiDynamic::resolveQRCode($qrcode);

        $url = (new UpiDynamic)->paymentUrl();

        return redirect($url);
    }
}
