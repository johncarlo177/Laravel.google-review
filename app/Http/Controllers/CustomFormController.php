<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use App\Support\CustomForms\CustomFormsManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class CustomFormController extends Controller
{
    use WriteLogs;

    protected CustomFormsManager $forms;

    public function __construct()
    {
        $this->forms = new CustomFormsManager;
    }

    public function show(CustomForm $customForm)
    {
        return $customForm;
    }

    public function store(Request $request)
    {
        return $this->saveForm($request);
    }

    public function saveSettings(CustomForm $customForm, Request $request)
    {
        return $this->forms->saveSettings(
            $customForm,
            $request->input('settings')
        );
    }

    public function update(CustomForm $customForm, Request $request)
    {
        return $this->saveForm($request, $customForm);
    }

    private function saveForm(Request $request, ?CustomForm $customForm = null)
    {
        return $this->forms->save(
            id: $customForm?->id,
            name: $request->input('name'),
            fields: $request->input('fields'),
            user: $request->user(),
            type: $request->input('type'),
            related_model: $request->input('related_model'),
            related_model_id: $request->input('related_model_id'),
        );
    }
}
