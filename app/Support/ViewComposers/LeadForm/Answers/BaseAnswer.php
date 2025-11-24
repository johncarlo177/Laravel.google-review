<?php

namespace App\Support\ViewComposers\LeadForm\Answers;

use App\Support\ViewComposers\BaseComposer;
use Illuminate\View\View;

abstract class BaseAnswer extends BaseComposer
{
    protected $question = null;

    public abstract static function type(): string;

    public static function path(): string
    {
        return 'qrcode.components.lead-form.answers.' . static::type();
    }

    public function compose(View $view)
    {
        parent::compose($view);

        $this->question = @$view->getData()['question'];
    }
}
