<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class PhoneAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'phone';
    }

    public function placeholderText()
    {
        $placeholder = @$this->question['placeholder_text'];

        return $placeholder ?? t('Type your answer here ...');
    }
}
