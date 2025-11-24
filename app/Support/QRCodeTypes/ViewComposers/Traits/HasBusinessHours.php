<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

use App\Support\Color;
use Throwable;

trait HasBusinessHours
{
    protected abstract function designValue($key);

    public function businessHoursStyles()
    {
        $selector = 'html .qrcode-type .layout-generated-webpage .opening-hours .row:not(.additional):not(:first-child)';

        $textColor = $this->designValue('textColor');

        if (empty($textColor)) return;

        list($r, $g, $b) = Color::rgb($textColor);

        $color = "rgba($r, $g, $b, 0.2)";

        return sprintf('%s { border-color: %s; }', $selector, $color);
    }

    public function isOpeningHourAdditional($day)
    {
        return preg_match('/_/', $day);
    }


    public function openingHourDayName($day)
    {
        if (preg_match('/_/', $day)) return '';

        return t($day);
    }

    private function getOpeningHours($value)
    {

        try {
            $hours = (array)$value;

            if (empty($hours) || !is_array($hours)) {
                return [];
            }

            $hours = $this->sortOpeningHours($hours);

            return $hours;
        } catch (Throwable $th) {
            return [];
        }
    }

    private function sortOpeningHours($hours)
    {
        $days = array_keys($hours);

        $defaultDays = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        usort($days, function ($a, $b) use ($defaultDays) {
            if ($a === $b) return 0;

            // base name
            $bn = fn ($v) => explode('_', $v)[0];

            $i = fn ($v) => @explode('_', $v)[1] ?? 0;

            if ($bn($a) === $bn($b)) {
                return $i($a) - $i($b);
            }

            return array_search($bn($a), $defaultDays) - array_search($bn($b), $defaultDays);
        });

        return array_reduce($days, function ($result, $day) use ($hours) {
            $result[$day] = $hours[$day];
            return $result;
        }, []);
    }

    private function defaultOpeningHours()
    {
        $days = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ];

        return array_reduce($days, function ($result, $day) {
            $result[$day] = (object)[
                'enabled' => true,
                'from' => '09:00 AM',
                'to' => '05:00 PM'
            ];

            return $result;
        }, []);
    }

    public function openingHours()
    {
        if (empty($this->fetchOpeningHours())) {
            return $this->defaultOpeningHours();
        }

        return $this->getOpeningHours(
            $this->fetchOpeningHours()
        );
    }

    private function fetchOpeningHours()
    {
        return $this->qrcodeData('openingHours');
    }
}
