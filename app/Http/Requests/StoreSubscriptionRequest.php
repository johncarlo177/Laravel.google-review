<?php

namespace App\Http\Requests;

use App\Models\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
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
     * @return array
     */
    public function rules()
    {
        return [
            'subscription_plan_id' => 'required|exists:subscription_plans,id',

            'user_id' => 'required|exists:users,id',

            'subscription_status' => [
                'required',
                Rule::in(SubscriptionStatus::getStatuses())
            ]
        ];
    }

    public function attributes()
    {
        return [
            'user_id' => 'user',
            'subscription_plan_id' => 'subscription plan',
        ];
    }
}
