<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class TextAreaAnswer extends TextAnswer
{
    public static function type(): string
    {
        return 'textarea';
    }
}
