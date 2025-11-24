<?php

namespace App\Console;

use App\Jobs\DemoCleaner;
use App\Jobs\ScanRetentionJob;
use App\Jobs\TrialUsersCleaner;
use App\Models\Config;
use App\Models\Subscription;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Notifications\Dynamic\DynamicNotificationsManager;
use App\Repositories\SubscriptionManager;
use App\Support\ConfigHelper;
use App\Support\DropletManager;
use App\Support\FaviconManager;
use App\Support\FileOwnership;
use App\Support\QRCodeManager;
use App\Support\QRCodeScanManager;
use App\Support\System\LogFileManager;
use App\Support\System\Traits\WriteLogs;
use Throwable;

class Kernel extends ConsoleKernel
{
    use WriteLogs;

    const CRON_LAST_RUN_CONFIG_KEY = 'cron.last_run';

    private static $schedules = [];

    public static function addSchedule($callback)
    {
        static::$schedules[] = $callback;
    }

    private function runSchedules(Schedule $schedule)
    {
        foreach ($this::$schedules as $callback) {
            try {
                $callback($schedule);
            } catch (Throwable $th) {
                $this->logError('Error running schedule. ' . $th->getMessage());
            }
        }
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            // Fix storage file permission issue in docarized platform
            FileOwnership::setStorageOwnership();
        })->everyTenMinutes();

        $schedule->call(function () {
            Config::set(static::CRON_LAST_RUN_CONFIG_KEY, time());
        })->everyMinute();

        $schedule->call(function () {
            FaviconManager::publish();
        })->twiceDaily();

        $schedule->call(function () {
            $dropletManager = new DropletManager;
            try {
                $dropletManager->verify();
            } catch (Throwable $th) {
                //
            }
        })->daily();

        $schedule->call(function () {
            DynamicNotificationsManager::broadcast();
        })->twiceDaily();

        $schedule->call(function () {
            if (ConfigHelper::isNotEnabled('reset_scans_every_month')) return;

            (new QRCodeScanManager)->resetAllUsersScans();
            // 
        })->monthlyOn(1);

        $schedule->call(function () {
            /**
             * @var SubscriptionManager
             */
            $subscriptions = app(SubscriptionManager::class);

            $subscriptions->setExpiredSubscriptions(Subscription::get()->all());
        })->daily();

        $schedule->call(
            function () {
                // 
                $log = new LogFileManager;
                $log->clearIfExceededMaxSize();
                // 
            }
        )->daily();

        $schedule->call(function () {
            (new QRCodeManager)->deleteRecentlyDeletedAutomatically();
        })->daily();

        $this->runSchedules($schedule);

        $schedule->job(ScanRetentionJob::class)->daily();

        $schedule->job(DemoCleaner::class)->twiceDaily();

        $schedule->job(TrialUsersCleaner::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
