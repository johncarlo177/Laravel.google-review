<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\QRCode;
use App\Support\QRCodeScanManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Cache;
use Throwable;

class QRCodeScansCalculator extends Seeder
{
    use WriteLogs;

    public function versionUpgradeRequired()
    {
        return $this->shouldRun();
    }

    protected function shouldRun()
    {
        try {
            $this->markAsCompletedIfNeeded();
        } catch (Throwable $th) {
            // 
        }

        return !$this->isCompleted();
    }

    private function isCompleted()
    {
        return Cache::get(sprintf('%s::%s', static::class, 'is_completed'));
    }

    private function markAsCompletedIfNeeded()
    {
        if ($this->isCompleted()) {
            return;
        }

        if (!$this->queryHasResults()) {
            $this->markAsCompleted();
        }
    }

    private function markAsCompleted()
    {
        Cache::set(sprintf('%s::%s', static::class, 'is_completed'), true);
    }

    protected function chunkSize()
    {
        return 100;
    }

    protected function currentOffsetKey()
    {
        return sprintf('%s::%s', static::class, 'current_offset');
    }

    protected function setCurrentOffset($v)
    {
        Cache::set($this->currentOffsetKey(), $v);
    }

    protected function getCurrentOffset()
    {
        $value = Cache::get($this->currentOffsetKey());

        if (empty($value)) return 0;

        return Cache::get($this->currentOffsetKey());
    }

    protected function query()
    {
        return QRCode::whereNull('scans_count')
            ->orderBy('id', 'desc')
            ->limit($this->chunkSize())
            ->skip($this->getCurrentOffset() * $this->chunkSize());
    }

    protected function queryHasResults()
    {
        return $this->query()->count() > 0;
    }

    protected function run()
    {
        static::logger()->logInfo('Current chunk offset: %s', $this->getCurrentOffset());

        $qrcodes = $this->query()
            ->get();

        if ($qrcodes->isEmpty()) {
            $this->markAsCompleted();
            return;
        }

        $scans = new QRCodeScanManager;

        $qrcodes->each(function (QRCode $qrcode) use ($scans) {
            $scans->syncCountOfQRCodeScans($qrcode);
        });

        $this->setCurrentOffset($this->getCurrentOffset() + 1);
    }
}
