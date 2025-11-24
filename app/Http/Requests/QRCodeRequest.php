<?php

namespace App\Http\Requests;

use App\Interfaces\SubscriptionManager;
use App\Interfaces\UserManager;
use App\Models\QRCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class QRCodeRequest extends FormRequest
{
    private SubscriptionManager $subscriptions;

    public function __construct()
    {
        $this->subscriptions = app(SubscriptionManager::class);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [
                'required',
                Rule::in($this->getAvailableQrTypes())
            ]
        ];
    }

    public function messages()
    {
        return [
            'type.in' => $this->getTypeErrorMessage()
        ];
    }

    private function getTypeErrorMessage()
    {
        if (empty($this->type)) {
            return sprintf('%s %s', t('Type'), t('field is required.'));
        }

        return t('Your plan does not allow to use this type.');
    }

    private function getAvailableQrTypes()
    {
        if (!$this->subscriptions->shouldEnforceSubscriptionRules($this->user())) {
            return QRCode::getTypes();
        }


        return $this->users()->getCurrentPlan($this->user())->qr_types;
    }

    /** @return \App\Interfaces\UserManager */
    private function users()
    {
        return app(UserManager::class);
    }
}
