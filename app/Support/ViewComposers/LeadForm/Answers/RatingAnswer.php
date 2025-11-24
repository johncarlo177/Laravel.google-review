<?php

namespace App\Support\ViewComposers\LeadForm\Answers;

class RatingAnswer extends BaseAnswer
{
    public static function type(): string
    {
        return 'rating';
    }

    private function defaultFrom()
    {
        return 1;
    }

    private function defaultTo()
    {
        return 10;
    }

    private function defaultRange()
    {
        return range($this->defaultFrom(), $this->defaultTo());
    }

    private function range()
    {
        return range($this->from(), $this->to());
    }

    private function num($key, $default)
    {
        $value = @$this->question['rating_' . $key] ?? $default;

        $value = +$value;

        if (!is_integer($value)) {
            return $default;
        }

        return $value;
    }

    private function from()
    {
        return $this->num('from', $this->defaultFrom());
    }

    private function to()
    {
        return $this->num('to', $this->defaultTo());
    }

    public function ratingNumbers()
    {
        if ($this->from() > $this->to()) {
            return $this->defaultRange();
        }

        return $this->range();
    }
}
