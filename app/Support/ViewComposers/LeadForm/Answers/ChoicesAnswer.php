<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class ChoicesAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'choices';
    }

    public function choices()
    {
        $value = @$this->question['choices'];

        if (empty($value)) {
            return [];
        }

        return explode("\n", $value);
    }

    public function isMultiple()
    {
        return @$this->question['is_multiple'] === 'multiple';
    }
}
