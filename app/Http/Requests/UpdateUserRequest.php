<?php

namespace App\Http\Requests;

use App\Support\MobileNumberManager;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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

            'email' => 'required|email',

            'password' => ['confirmed', Password::min(6)]
        ];
    }

    public function setValidator(Validator $validator)
    {
        MobileNumberManager::extendValidator($validator);

        return parent::setValidator($validator);
    }
}
