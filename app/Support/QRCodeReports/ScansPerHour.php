<?php

namespace App\Support\QRCodeReports;

class ScansPerHour extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-hour';
    }

    protected function reportColumn()
    {
        return 'hour';
    }

    protected function orderBy()
    {
        $this->query->orderBy('hour', 'asc');
    }

    protected function padResult()
    {
        $hours = collect(range(0, 23))
            ->map(
                fn ($h) => str_pad($h, 2, '0', STR_PAD_LEFT)
            );

        $this->result = $hours->map(function ($hour) {
            $item = $this->result->first(fn ($result) => $result['hour'] == $hour);

            $scans = $item ? $item['scans'] : 0;

            return compact('hour', 'scans');
        })->values();
    }
}
