<?php

namespace App\Support\Dashboard\MultiQRCodes;

use App\Models\QRCode;
use App\Models\QRCodeScan;
use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection<DateRange> monthlyRange
 * @property Collection<QRCodeScan> scans
 */
class Item
{
    use WriteLogs;

    protected QRCode $qrcode;
    protected $scans;
    protected Carbon $fromDate;
    protected Carbon $toDate;

    protected $monthlyRange;

    public $android;
    public $iOS;
    public $mac;
    public $windows;
    public $linux;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function withScans($scans)
    {
        $this->scans = $scans->filter(
            function (QRCodeScan $scan) {
                return $scan->qrcode_id == $this->qrcode->id;
            }
        )->values();

        return $this;
    }

    protected function filterScansByDates()
    {
        $this->scans = $this->scans->filter(
            function (QRCodeScan $scan) {
                return $scan->created_at->isBetween($this->fromDate, $this->toDate);
            }
        );

        return $this;
    }

    public function build()
    {
        $this->filterScansByDates();

        $this->android = $this->scans->filter(
            function (QRCodeScan $scan) {
                return strtolower($scan->os_name) === 'android';
            }
        )->count();

        $this->iOS = $this->scans->filter(
            function (QRCodeScan $scan) {
                return strtolower($scan->os_name) === 'ios';
            }
        )->count();

        $this->mac = $this->scans->filter(
            function (QRCodeScan $scan) {
                return strtolower($scan->os_name) === 'mac';
            }
        )->count();

        $this->windows = $this->scans->filter(
            function (QRCodeScan $scan) {
                return strtolower($scan->os_name) === 'windows';
            }
        )->count();

        $this->linux = $this->scans->filter(
            function (QRCodeScan $scan) {
                return strtolower($scan->os_name) === 'linux';
            }
        )->count();

        $this->buildMonthlyRange();

        return $this;
    }

    protected function getMonthlyRangeArray()
    {
        return $this->monthlyRange->reduce(
            function ($result, DateRange $range) {

                $result[$range->name] = $range->value;

                return $result;
            },
            []
        );
    }

    /**
     * @return Collection<DateRange>
     */
    protected function buildMonthlyRange()
    {
        $count = $this->toDate->diffInMonths($this->fromDate, true);

        $count = max($count, 1);

        $this->monthlyRange = collect(
            range(0, $count)
        )
            ->map(
                function ($i) {
                    // 
                    $startingMonth = $this->fromDate
                        ->clone()
                        ->addMonth($i)
                        ->day(1)
                        ->minute(0)
                        ->hour(0)
                        ->second(0);

                    $endMonth = $startingMonth
                        ->clone()
                        ->addMonth(1)
                        ->day(1)
                        ->minute(0)
                        ->hour(0)
                        ->second(0);

                    $range = new DateRange;

                    $range->name = $startingMonth->format('F');

                    $range->from = $startingMonth;

                    $range->to = $endMonth;

                    $range->value = $this->scans->filter(
                        function (QRCodeScan $scan) use ($range) {
                            return $scan
                                ->created_at
                                ->isBetween(
                                    $range->from,
                                    $range->to
                                );
                        }
                    )->count();

                    return $range;
                }
            );

        $this->monthlyRange = $this->monthlyRange->reverse()->values();

        return $this;
    }

    public function from($date)
    {
        $this->fromDate = $date;

        return $this;
    }

    public function to($date)
    {
        $this->toDate = $date;

        return $this;
    }

    public function toArray()
    {
        return [
            'qrcode' => [
                'id' => $this->qrcode->id,
                'name' => $this->qrcode->name,
                'type' => $this->qrcode->type,
            ],
            'total_scans' => $this->scans->count(),
            'android' => $this->android,
            'iOS' => $this->iOS,
            'mac' => $this->mac,
            'windows' => $this->windows,
            'linux' => $this->linux,
            'months' => $this->monthlyRange
        ];
    }
}
