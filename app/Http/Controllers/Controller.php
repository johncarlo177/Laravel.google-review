<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct() {}

    protected function restrictDemo()
    {
        if (env('ALLOW_ADDING_NEW_PAYMENT_PROCESSOR_IN_DEMO')) {
            return;
        }

        if (app()->environment('demo')) {
            ErrorMessageMiddleware::setMessage(t('This action is disabled in demo.'));

            abort(403);
        }
    }

    protected function failWithValidationError($key, $message)
    {
        $validator = Validator::make([], []);

        $validator->after(function () use ($validator, $key, $message) {
            $validator->errors()->add($key, $message);
        });

        $validator->validate();
    }

    protected function optionalUser(): ?User
    {
        $user = null;

        if (request()->bearerToken() && $user = Auth::guard('sanctum')->user()) {
            Auth::setUser($user);
        }

        return $user;
    }
}
