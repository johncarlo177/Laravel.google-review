<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class TextAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'text';
    }

    public function placeholderText()
    {
        $placeholder = @$this->question['placeholder_text'];

        return $placeholder ?? t('Type your answer here ...');
    }
}
