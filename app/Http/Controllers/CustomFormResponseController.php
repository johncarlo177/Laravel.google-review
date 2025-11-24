<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Policies\QRCodePolicy;
use App\Support\CustomForms\CustomFormsManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class CustomFormResponseController extends Controller
{
    use WriteLogs;

    private CustomFormsManager $forms;

    public function __construct()
    {
        parent::__construct();

        $this->forms = new CustomFormsManager();
    }

    public function deleteResponse(CustomFormResponse $customFormResponse)
    {
        $this->authorizeDelete($customFormResponse);

        $customFormResponse->delete();
    }

    private function authorizeDelete(CustomFormResponse $response)
    {
        if (QRCodePolicy::canListAll($this->optionalUser())) {
            return true;
        }

        $form = $response->custom_form;

        $qrcode = $form->getQRCode();

        if (!$qrcode) {
            return false;
        }

        $authorized = $qrcode->user_id == $this->optionalUser()->id;

        if (!$authorized) {
            abort(401);
        }
    }

    public function saveResponse(CustomForm $customForm, Request $request)
    {
        $response = $this->forms->saveResponse(
            $customForm,
            $request->input('fields'),
            $this->optionalUser()
        );

        $response->load('custom_form');

        return $response;
    }

    public function updateResponse(
        CustomFormResponse $response,
        Request $request
    ) {
        $response->fields = $request->input('fields');

        $response->save();

        return $response;
    }

    public function show(CustomFormResponse $customFormResponse)
    {
        $customFormResponse->load('custom_form');

        if (!$this->authorizeView($customFormResponse)) {
            abort(401);
        }

        return $customFormResponse;
    }

    public function showAutomaticPopupResponses(CustomForm $customForm)
    {
        if ($customForm->getQRCode()?->user_id != $this->optionalUser()->id) {
            $this->logDebug('qrcode owner is different than current user.');
            return [];
        }

        return CustomFormResponse::where('custom_form_id', $customForm->id)->get();
    }

    private function authorizeView(CustomFormResponse $customFormResponse)
    {
        $user = $this->optionalUser();

        if (QRCodePolicy::canListAll($this->optionalUser())) return true;

        if (!$user) {
            return false;
        }

        return $user->id == $customFormResponse->custom_form->user_id ||
            $user->id == $customFormResponse->user_id;
    }
}
