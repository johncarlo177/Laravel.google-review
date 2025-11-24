<?php

namespace App\Support\QRCodeReports;

use Carbon\Carbon;

class ScansPerDay extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-day';
    }

    protected function reportColumn()
    {
        return '';
    }

    protected function selectReportColumn()
    {
        $this->query
            ->selectRaw('Date(created_at) as date')
            ->orderBy('date', 'DESC');
    }

    protected function applyGroup()
    {
        $this->query->groupBy('date');
    }

    protected function padResult()
    {
        $days = collect(
            range(0, $this->to->diffInDays($this->from, true))
        )
            ->map(
                function ($number) {
                    return $this->to->clone()->subDays($number)->format('Y-m-d');
                }
            )->reverse();

        $paddedResult = $days->map(function ($date) {

            $entry = $this->result->first(
                fn($item) => $item['date'] == $date
            );

            return [
                'scans' => @$entry['scans'] ?? 0,
                'date' => $date
            ];
        })->values();

        $this->result = $paddedResult;
    }

    protected function formatResult()
    {
        $this->result = collect($this->result)->map(function ($item) {
            return array_merge($item, [
                'date' => $this->formatDate($item['date'])
            ]);
        })->values();
    }

    private function formatDate($date)
    {
        $carbon = new Carbon($date);

        $month = $carbon->format('M');

        $day = $carbon->format('d');

        return sprintf('%s %s', t($month), $day);
    }
}
