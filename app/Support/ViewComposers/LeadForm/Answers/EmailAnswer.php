<?php

namespace App\Support\ViewComposers\LeadForm\Answers;

class EmailAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'email';
    }

    public function placeholderText()
    {
        $placeholder = @$this->question['placeholder_text'];

        return $placeholder ?? t('Type your answer here ...');
    }
}
