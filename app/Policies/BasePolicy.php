<?php

namespace App\Policies;

use App\Http\Middleware\ErrorMessageMiddleware;


abstract class BasePolicy
{
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

    protected function fail($message)
    {
        ErrorMessageMiddleware::setMessage($message);
        abort(403);
    }
}
