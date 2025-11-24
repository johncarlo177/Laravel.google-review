<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Http\Responses\QRCodeTemplateResponse;
use App\Interfaces\SubscriptionManager;
use App\Models\QRCode;
use App\Models\QRCodeTemplate;
use App\Models\User;
use App\Policies\Restriction\QRCodeRestrictor;
use App\Support\QRCodeTemplateManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QRCodeTemplatesController extends Controller
{
    private QRCodeTemplateManager $templates;

    private SubscriptionManager $subscriptions;

    public function __construct(QRCodeTemplateManager $templates, SubscriptionManager $subscriptions)
    {
        $this->templates = $templates;
        $this->subscriptions = $subscriptions;
    }

    public function index(Request $request)
    {
        return QRCodeTemplateResponse::list(
            $this->templates->getTemplatesForUser($request->user())
        );
    }

    public function getTemplate(QRCodeTemplate $qrcodeTemplate)
    {
        return QRCodeTemplateResponse::single($qrcodeTemplate);
    }

    public function useInExistingQRCode(QRCodeTemplate $template)
    {
        $destination = QRCode::findOrFail(request()->input('qrcode_id'));

        if (!$this->user()->permitted('qrcodes.list-all')) {
            if ($destination->user_id != $this->user()->id) {
                abort(401, 'Ownership validation error');
            }
        }

        return $this->templates->useInExisting(
            $template,
            $destination
        );
    }

    public function useTemplate(QRCodeTemplate $qrcodeTemplate)
    {
        if (
            !$this
                ->subscriptions
                ->allowedToCreateDynamicQRCodes(
                    $this->user(),
                    1,
                    $qrcodeTemplate->type
                )
        ) {
            ErrorMessageMiddleware::abortWithMessage(
                t('QR Code Limit Reached'),
                422
            );
        }

        return $this->templates->use(
            $qrcodeTemplate,
            request()->user()
        );
    }

    public function saveTemplate(Request $request)
    {
        $request->validate([
            'qrcode_id' => ['required', Rule::in(QRCode::pluck('id'))],
            'name' => ['required']
        ]);

        QRCodeRestrictor::make($request->qrcode_id)->applyRestrictions();

        $this->validateOwnership($request);

        $template_access_level = QRCodeTemplate::TEMPLATE_ACCESS_LEVEL_PRIVATE;

        if ($request->user()->permitted('qrcode-template.manage-all')) {
            $template_access_level = $request->input('template_access_level');
        }

        return QRCodeTemplateResponse::single(
            $this->templates->save(
                qrcodeId: $request->input('qrcode_id'),
                name: $request->input('name'),
                description: $request->input('description'),
                screenshot_id: $request->input('screenshot_id'),
                template_access_level: $template_access_level,
                category_id: $request->input('category_id'),
                video_id: $request->input('video_id'),
            )
        );
    }

    private function user(): User
    {
        return request()->user();
    }

    private function validateOwnership(Request $request)
    {
        $qrcode = QRCode::find($request->input('qrcode_id'));

        if ($this->user()->permitted('qrcode-template.manage-all')) return true;

        if ($qrcode->user_id != $this->user()->id) {
            abort(401);
        }
    }
}
