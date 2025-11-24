<?php

namespace App\Support;

use App\Models\QRCode;
use App\Models\QRCodeTemplate;
use App\Models\User;

class QRCodeTemplateManager
{
    private QRCodeManager $qrcodes;

    public function __construct()
    {
        $this->qrcodes = new QRCodeManager();
    }

    private function query()
    {
        $q = QRCodeTemplate::query();

        return $q;
    }

    public function useInExisting(QRCodeTemplate $template, QRCode $destination)
    {
        /**
         * Create temporary QR code to copy all files and lead forms of the
         * main template.
         */
        $tmp = $this->qrcodes->copy($this->toQRCode($template))[0];

        $tmp->refresh();

        // Delete existing files (if any).
        QRCodeAttachedFiles::withQRCode($destination)->deleteFiles();

        $destination->data = $tmp->data;
        $destination->type = $tmp->type;
        $destination->design = $tmp->design;

        $destination->save();

        if ($tmp->getWebPageDesign()?->design) {
            // 
            $page = (new QRCodeWebPageDesignManager)->getDesignOrCreateNewDesignIfNeeded($destination);

            $page->design = $tmp->getWebPageDesign()->design;

            $page->save();

            $tmp->getWebPageDesign()->delete();
        }

        $tmp->redirect->delete();
        $tmp->delete();

        $destination->refresh();

        return $destination;
    }

    public function use(QRCodeTemplate $qrcodeTemplate, User $user)
    {
        $newQRCode = $this->qrcodes->copy($this->toQRCode($qrcodeTemplate))[0];

        $this->qrcodes->changeUser($newQRCode, $user->id);

        $newQRCode->refresh();

        return $newQRCode;
    }

    public function getTemplatesForUser(User $user)
    {
        return $this->query()
            ->where('is_template', true)
            ->where(function ($query) use ($user) {

                $query
                    ->where('user_id', $user->id)
                    ->orWhere(
                        'template_access_level',
                        QRCodeTemplate::TEMPLATE_ACCESS_LEVEL_PUBLIC
                    );
            })
            ->get();
    }

    public function toQRCode(QRCodeTemplate $template): ?QRCode
    {
        return QRCode::find($template->id);
    }

    public function toTemplate(QRCode $qrcode): ?QRCodeTemplate
    {
        return QRCodeTemplate::find($qrcode->id);
    }

    public function save(
        $qrcodeId,
        $name,
        $description,
        $screenshot_id,
        $template_access_level,
        $category_id,
        $video_id = null,
    ) {
        /**
         * @var QRCodeTemplate
         */
        $template = QRCodeTemplate::find($qrcodeId);

        $template->is_template = true;

        $template->name = $name;

        $template->template_access_level = $template_access_level;

        $template->template_category_id = $category_id;

        $template->save();

        // Description and screenshot_id are meta attributes.
        $template->description = $description;

        $template->screenshot_id = $screenshot_id;

        $template->video_id = $video_id;

        return $template;
    }
}
