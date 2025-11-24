<?php

namespace App\Support\CustomForms;

use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Models\User;
use App\SUpport\CustomForms\ResponseHandlers\BaseResponseHandler;
use App\Support\CustomForms\ResponseHandlers\Finder as HandlerFinder;
use App\Support\System\Traits\WriteLogs;

class CustomFormsManager
{
    use WriteLogs;

    public function saveSettings(CustomForm $form, $settings)
    {
        $form->settings = $settings;
        $form->save();

        return $form;
    }

    public function save(
        $id = null,
        $name = null,
        $fields = [],
        User $user,
        $type = null,
        $related_model = null,
        $related_model_id = null
    ) {
        if (!$id) {
            $form = new CustomForm();
            $form->user_id = $user->id;
        } else {
            $form = CustomForm::find($id);
        }

        $form->name = $name;

        $form->fields = $fields;

        $form->type = $type;

        $form->related_model = $related_model;

        $form->related_model_id = $related_model_id;

        $form->save();

        return $form;
    }

    public function saveResponse(
        CustomForm $customForm,
        $fields,
        ?User $user = null
    ) {
        $response = new CustomFormResponse();

        $response->custom_form_id = $customForm->id;

        $response->fields = $fields;

        $response->user_id = $user?->id;

        $response->save();

        HandlerFinder::ofForm($customForm)
            ->getHandlers()->each(
                function (BaseResponseHandler $handler) use ($response) {
                    $handler->handleResponse($response);
                }
            );

        return $response;
    }
}
