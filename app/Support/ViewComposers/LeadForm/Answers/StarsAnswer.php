<?php

namespace App\Support\ViewComposers\LeadForm\Answers;


class StarsAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'stars';
    }

    private function defaultNumberOfStars()
    {
        return 7;
    }

    public function stars()
    {
        $number = $this->question['number_of_stars'] ?? $this->defaultNumberOfStars();

        $number = $number < 1 ? $this->defaultNumberOfStars() : $number;

        return range(1, $number);
    }

    public function questionData()
    {
        return base64_encode(json_encode($this->question ?? []));
    }
}
