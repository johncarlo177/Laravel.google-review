<?php

namespace App\Http\Requests;

use App\Rules\DomainRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'host' => ['required', new DomainRule],
            'protocol' => 'required|in:https,http',
        ];
    }
}
