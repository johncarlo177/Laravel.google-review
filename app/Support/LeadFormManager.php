<?php

namespace App\Support;

use App\Models\LeadForm;
use App\Models\LeadFormResponse;
use App\Models\QRCode;
use App\Models\QRCodeWebPageDesign;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;

class LeadFormManager
{
    use WriteLogs;

    private QRCodeWebPageDesignManager $webpageDesignManager;

    protected $shouldIncludedEmptyForms = false;

    public function __construct()
    {
        $this->webpageDesignManager = new QRCodeWebPageDesignManager;
    }

    public function withEmptyResponses($shouldIncludedEmptyForms)
    {
        $this->shouldIncludedEmptyForms = $shouldIncludedEmptyForms;

        return $this;
    }

    public function buildUserQuery(User $user)
    {
        $ids = $this->getUserLeadFormIds($user);

        $query  = LeadForm::whereIn('id', $ids);

        if ($this->shouldIncludedEmptyForms) {
            return $query;
        }

        return $query->whereHas('responses');
    }

    private function getUserLeadFormIds(User $user)
    {
        $qrcodeManager = new QRCodeManager();

        $query = $qrcodeManager->buildUserSearchQuery(actor: $user);

        $query->select('id');

        $qrcodes = $query->get();

        $qrcodeWebPageDesignManager = new QRCodeWebPageDesignManager;

        $qrcodeWebPageDesigns = $qrcodeWebPageDesignManager
            ->getDesignsOfQRCodeIds(
                $qrcodes->pluck('id')
            )->pluck('id');

        return LeadForm::with('user')
            ->where(function ($query) use ($qrcodes) {
                $query
                    ->where('related_model', class_basename(QRCode::class))
                    ->whereIn('related_model_id', $qrcodes->pluck('id'));
            })
            ->orWhere(function ($query) use ($qrcodeWebPageDesigns) {
                $query
                    ->where('related_model', class_basename(QRCodeWebPageDesign::class))
                    ->whereIn('related_model_id', $qrcodeWebPageDesigns);
            })->pluck('id');
    }

    public function save(
        $id = null,
        string $related_model_id,
        string $related_model,
        $configs,
        $fields,
        User $user
    ) {

        if (empty($id)) {
            $form = $this->createForm($user);
        } else {
            $form = LeadForm::find($id);
        }

        $form->related_model = $related_model;

        $form->related_model_id = $related_model_id;

        $form->configs = $configs;

        $form->fields = $fields;

        $form->save();

        return $form;
    }

    private function ofQRCodes($qrcodeIds)
    {
        return LeadForm::where(function ($query) use ($qrcodeIds) {

            $query->where('related_model', class_basename(QRCode::class))
                ->whereIn('related_model_id', $qrcodeIds);
            //
        })->orWhere(function ($query) use ($qrcodeIds) {

            $designs = $this->webpageDesignManager->getDesignsOfQRCodeIds($qrcodeIds);

            $designIds = $designs->pluck('id');

            $this->logDebug('design ids = %s', json_encode($designIds));

            $query->where('related_model', class_basename(QRCodeWebPageDesign::class))
                ->whereIn('related_model_id', $designIds);
            //
        })->get();
    }

    public function deleteQRCodesForms($qrcodeIds)
    {
        $forms = $this->ofQRCodes($qrcodeIds);

        $this->logDebug('forms = %s', json_encode($forms->pluck('id')));

        return $this->deleteForms($forms);
    }

    private function deleteForms($forms)
    {
        collect($forms)->each(function (LeadForm $form) {
            $this->deleteResponses($form->id);
            $form->delete();
        });
    }

    private function deleteForm(LeadForm $form)
    {
        return $this->deleteForms([$form]);
    }

    private function deleteResponses($formId)
    {
        LeadFormResponse::where('lead_form_id', $formId)->delete();
    }

    public function getQRCode(LeadForm $leadForm): ?QRCode
    {
        $qrcodeId = null;

        switch ($leadForm->related_model) {
            case class_basename(QRCodeWebPageDesign::class):
                $qrcodeId = QRCodeWebPageDesign::find($leadForm->related_model_id)?->qrcode_id;
                break;

            case class_basename(QRCode::class):
                $qrcodeId = $leadForm->related_model_id;
                break;
        }

        return QRCode::find($qrcodeId);
    }

    private function createForm(User $user)
    {
        $form = new LeadForm();

        $form->user_id = $user->id;

        return $form;
    }
}
