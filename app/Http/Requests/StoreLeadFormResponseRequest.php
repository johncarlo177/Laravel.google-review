<?php

namespace App\Http\Requests;

use App\Models\LeadForm;
use App\Support\LeadFormResponseManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadFormResponseRequest extends FormRequest
{
    private Validator $validatorInstance;

    private LeadFormResponseManager $leads;

    public function __construct(LeadFormResponseManager $leads)
    {
        $this->leads = $leads;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fields' => 'required',
            'fingerprint' => 'required',
            'lead_form_id' => [
                'required', Rule::in(LeadForm::pluck('id')->values()->all())
            ]
        ];
    }

    public function withValidator($validator)
    {
        $this->validatorInstance = $validator;

        $this->validatorInstance->after(function () {
            $this->validateSingleFormSubmission();
        });
    }

    private function validateSingleFormSubmission()
    {
        if ($this->leadForm()->isMultipleSubmissionAllowed()) {
            return;
        }

        if (!$this->fingerprintFound()) {
            return;
        }

        $this->validatorInstance->errors()->add(
            'fingerprint',
            'Multiple submission is not allowed for this form'
        );
    }

    private function fingerprintFound()
    {
        return $this->leads->findFingerprint(
            lead_form_id: $this->input('lead_form_id'),
            fingerprint: $this->input('fingerprint')
        );
    }

    private function leadForm(): ?LeadForm
    {
        $id = $this->input('lead_form_id');

        return LeadForm::find($id);
    }
}
