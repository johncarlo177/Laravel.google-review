<?php

namespace App\Support\CustomForms\ResponseHandlers;

use App\Models\CustomForm;
use App\SUpport\CustomForms\ResponseHandlers\BaseResponseHandler;
use App\Support\System\ClassFinder;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Collection;

class Finder
{
    use WriteLogs;

    private CustomForm $form;

    static function ofForm(CustomForm $customForm)
    {
        $instance = new static;
        $instance->form = $customForm;

        return $instance;
    }
    /**
     * @return Collection<BaseResponseHandler>
     */
    public function getHandlers()
    {
        return ClassFinder::in(__DIR__)
            ->subClassesOf(BaseResponseHandler::class)
            ->find()
            ->filter(function (BaseResponseHandler $handler) {
                $type = $handler->type();

                $matched = preg_match("/$type/i", $this->form->type);

                $this->logDebug(
                    'matching %s with type (%s) against form type (%s) and result is (%s)',
                    $handler::class,
                    $handler->type(),
                    $this->form->type,
                    $matched ? 'true' : 'false'
                );

                return $matched;
            })->values();
    }
}
