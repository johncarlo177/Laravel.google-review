<?php

namespace App\Http\Requests;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSubscriptionPlanRequest extends FormRequest
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
            'name' => 'required',
            'frequency' => [Rule::in([
                SubscriptionPlan::FREQUENCY_YEARLY,
                SubscriptionPlan::FREQUENCY_MONTHLY,
                SubscriptionPlan::FREQUENCY_LIFETIME
            ])],
            'price' => 'required|numeric',
            'number_of_dynamic_qrcodes' => 'required|integer|min:-1',
            'number_of_scans' => 'required|integer|min:-1',
            'number_of_custom_domains' => 'required|integer|min:-1',
            'number_of_users' => 'integer|min:-1|max:1000',
            'file_size_limit' => 'integer|min:-1'
        ];
    }
}
