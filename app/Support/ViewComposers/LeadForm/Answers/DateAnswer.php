<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class DateAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'date';
    }

    public function placeholderText()
    {
        $placeholder = @$this->question['placeholder_text'];

        return $placeholder ?? t('Enter date ...');
    }
}
