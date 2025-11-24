<?php

namespace App\Support\SystemStatus;

use App\Console\Commands\AppInstall;
use App\Console\Kernel;
use App\Models\Config;
use App\Support\System\MemoryCache;
use Carbon\Carbon;

class CronjobEntry extends BaseEntry
{
    protected function getLastRunTime()
    {
        return MemoryCache::remember(
            __METHOD__,
            function () {

                return Config::getReal(Kernel::CRON_LAST_RUN_CONFIG_KEY);
                // 
            }
        );
    }

    protected function instructionsText()
    {
        return sprintf(
            'Add the following entry to your server cronjobs, it should run every 5 minutes (maximum allowed is 30 minutes): <p><strong>cPanel (and most other panels)</strong></p><code>%s</code> <p><strong>Hostinger</strong></p><code>wget -O /dev/null %s',
            AppInstall::cronjobCommand(),
            url('/system/cron'),
        );
    }

    protected function informationText()
    {
        return t('Last run on') . ' ' . (new Carbon($this->getLastRunTime()));
    }

    public function title()
    {
        return 'Cron job';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Running' : 'Not running';
    }

    protected function isSuccess()
    {
        if (isLocal()) {
            return true;
        }

        if (!$this->getLastRunTime()) {
            return false;
        }

        $date = new Carbon($this->getLastRunTime());

        $diff = abs(Carbon::now()->diffInMinutes($date));

        return $diff < 35;
    }

    public function sortOrder()
    {
        return 20;
    }
}
