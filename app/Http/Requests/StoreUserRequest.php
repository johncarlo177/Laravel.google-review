<?php

namespace App\Http\Requests;

use App\Rules\MobileNumberRule;
use App\Support\MobileNumberManager;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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

            'email' => 'required|email|unique:App\Models\User',

            'password' => [
                'required', 'confirmed', Password::min(6)
            ],

            'role_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => 'role'
        ];
    }

    public function setValidator(Validator $validator)
    {
        MobileNumberManager::extendValidator($validator);

        return parent::setValidator($validator);
    }
}
