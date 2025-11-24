<?php

namespace App\Policies\Restriction;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Support\System\Traits\WriteLogs;

abstract class BaseRestrictor
{
    use WriteLogs;

    protected $id;

    public abstract function getRestrictedIds();

    public static function make($id)
    {
        $instance = new static;

        $instance->id = $id;

        return $instance;
    }

    public function applyRestrictions()
    {
        if ($this->isRestricted()) {
            ErrorMessageMiddleware::abortWithMessage('Modification of this resource is disabled in demo.');
        }
    }

    public function isRestricted()
    {
        if (env('ALLOW_ADDING_NEW_PAYMENT_PROCESSOR_IN_DEMO')) {
            return false;
        }

        $restricted = app()->environment('demo') &&
            collect(
                $this->getRestrictedIds()
            )->filter(function ($id) {
                return $id == $this->id;
            })->isNotEmpty();

        return $restricted;
    }
}
