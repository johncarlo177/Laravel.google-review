<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCurrencyRequest;
use App\Interfaces\FileManager;
use App\Models\Config;
use App\Support\Mail\MailTester;
use App\Support\SoftwareUpdate\AutoUpdate\DownloadLinkGenerator;
use App\Support\SoftwareUpdate\AutoUpdate\SoftwareVersion;
use App\Support\SoftwareUpdate\AutoUpdate\UpdateRunner;
use App\Support\SoftwareUpdate\DatabaseUpdateManager;
use App\Support\System\CacheManager;
use App\Support\System\LogFileManager;
use App\Support\System\Traits\WriteLogs;
use App\Support\SystemStatus\SystemStatus;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Throwable;

class SystemController extends Controller
{
    use WriteLogs;

    private DatabaseUpdateManager $dbUpdateManager;

    private LogFileManager $logManager;

    private array $hiddenInDemo = [
        'services.google.api_key',
        'payment_processors.*',
        'filesystems.s3.*',
        'auth-workflow.*',
        'quickqr_art.api_key',
        'mail.*',
        'apple_wallet.*',
    ];

    public function __construct(DatabaseUpdateManager $dbUpdateManager, LogFileManager $logFileManager)
    {
        $this->dbUpdateManager = $dbUpdateManager;

        $this->logManager = $logFileManager;
    }

    public function status()
    {
        $status = new SystemStatus();

        return [
            'ok' => $status->ok() && !$this->dbUpdateManager->hasDatabaseUpdate(),
            'entries' => $status->get()
        ];
    }

    public function checkDatabaseUpdate()
    {
        return [
            'update_available' => $this->dbUpdateManager->hasDatabaseUpdate()
        ];
    }

    public function updateDatabase()
    {
        $this->dbUpdateManager->updateDatabaseIfNeeded();

        return [
            'updated' => true
        ];
    }

    public function getTimezones()
    {
        return DateTimeZone::listIdentifiers();
    }

    public function cron()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }

        ob_start();

        try {
            Artisan::call('schedule:run');
        } catch (Throwable $th) {
            //
            $this->logWarning('schedule:run failed.');
            $this->logWarning($th->getMessage());
        }

        try {
            Artisan::call('queue:work --stop-when-empty');

            // 
        } catch (Throwable $th) {
            //
            $this->logWarning('queue:work --stop-when-empty failed.');
            $this->logWarning($th->getMessage());
        }

        try {
            Artisan::call('queue:flush');
        } catch (Throwable $th) {
            //
            $this->logWarning('queue:flush failed.');
            $this->logWarning($th->getMessage());
        }

        ob_get_clean();
    }


    private function shouldRestrictDemo()
    {
        if (env('ALLOW_ADDING_NEW_PAYMENT_PROCESSOR_IN_DEMO')) {
            return false;
        }

        return true;
    }

    protected function shouldAllowSettingsAccess()
    {
        return request()->user()->permitted('system.settings');
    }

    public function saveConfigs(Request $request)
    {
        if ($this->shouldRestrictDemo()) {
            $this->restrictDemo();
        }

        if (!$this->shouldAllowSettingsAccess()) {
            abort(403);
        }

        $variables = collect($request->all())->filter(
            function ($input) {
                return is_array($input) && isset($input['key']);
            }
        )
            ->values()
            ->all();

        foreach ($variables as $input) {
            Config::set($input['key'], @$input['value']);
        }

        Config::rebuildCache();

        return array_map(
            function ($input) {
                return [
                    'key' => $input['key'],
                    'value' => Config::get($input['key'])
                ];
            },
            $variables
        );
    }

    protected function filterRequestedKeys($keys)
    {
        return $keys;
    }

    public function getConfigs(Request $request)
    {
        if (!$this->shouldAllowSettingsAccess()) {
            abort(403);
        }

        $keys = explode(',', $request->keys);

        $keys = $this->filterRequestedKeys($keys);

        $configsArray = array_map(function ($key) {
            return [
                'key' => $key,
                'value' => Config::get($key) ?? config($key)
            ];
        }, $keys);

        $configsArray = $this->hideInDemo($configsArray, $this->hiddenInDemo);

        return $configsArray;
    }

    public function uploadConfigAttachment(Request $request, FileManager $files)
    {
        $this->restrictDemo();

        $key = $request->key;

        if (!$request->user()->permitted('system.settings')) {
            abort(403);
        }

        if (!Config::getId($key)) {
            Config::set($key, null);
        }

        $request->merge([
            'attachable_type' => Config::class,
            'attachable_id' => Config::getId($key),
            'type' => FileManager::FILE_TYPE_CONFIG_ATTACHMENT
        ]);

        $file = $files->store($request);

        Config::set($key, $file->id);

        return $file;
    }

    public function testStorage()
    {
        /** @var FileManager */
        $files = app(FileManager::class);

        return [
            'result' => $files->testReadWrite()
        ];
    }

    public function serveLogs()
    {
        if (isDemo()) {
            return [
                'data' => base64_encode('Disabled in Demo mode.'),
                'size' => '0MB',
            ];
        }

        return [
            'data' => $this->logManager->getLogFileTail(true),
            'size' => $this->logManager->getLogFileSize(),
        ];
    }

    public function serveLogFile()
    {
        $this->restrictDemo();

        if (!URL::hasValidRelativeSignature(request())) {
            abort(401);
        }

        return $this->logManager->serve();
    }

    public function clearLogFile()
    {
        $this->restrictDemo();

        $this->logManager->clear();

        return [
            'result' => 'ok'
        ];
    }

    public function rebuildCache($type)
    {
        $this->restrictDemo();

        $isValid = CacheManager::validateType($type);

        if (!$isValid) {
            abort(422, 'Invalid type');
        }

        CacheManager::for($type)->rebuild();

        return [
            'success' => true
        ];
    }

    public function clearCache($type)
    {
        $this->restrictDemo();

        $isValid = CacheManager::validateType($type);

        if (!$isValid) {
            abort(422, 'Invalid type');
        }

        CacheManager::for($type)->clear();

        return [
            'success' => true
        ];
    }

    public function generateLogFileURL()
    {
        $this->restrictDemo();

        return [
            'url' => URL::signedRoute(
                name: 'log-file',
                absolute: false,
                expiration: now()->addMinute()
            )
        ];
    }

    private function hideInDemo($configsArray, $keysToHide)
    {
        if (env('ALLOW_ADDING_NEW_PAYMENT_PROCESSOR_IN_DEMO')) {
            return $configsArray;
        }

        if (!app()->environment('demo')) {
            return $configsArray;
        }

        return array_map(function ($config) use ($keysToHide) {
            $shouldHide = array_filter(
                $keysToHide,
                fn($key) => preg_match("/$key/", $config['key'])
            );

            if ($shouldHide) {
                return array_merge(
                    $config,
                    ['value' => 'hidden_in_demo']
                );
            }

            return $config;
        }, $configsArray);
    }

    public function testSmtp(Request $request)
    {
        $this->restrictDemo();

        $tester = new MailTester(
            to: $request->email,
            message: $request->message,
            subject: $request->subject
        );

        $tester->run();

        return [
            'debug' => $tester->getDebugLog()
        ];
    }

    public function getLatestVersion()
    {
        $version = new SoftwareVersion();

        return [
            'version' => $version->getLatestVersion()
        ];
    }

    public function updateSelf()
    {
        if (isDemo()) {

            sleep(3);

            $this->restrictDemo();
        }

        if (
            request()->host() === 'quickcode.test'
        ) {

            sleep(3);

            $this->logDebug(
                'Cannot self update on local development environement'
            );

            return [
                'result' => true,
                'subscription_required' => false,
            ];
        }

        $generator = new DownloadLinkGenerator;

        $link = $generator->generate();

        if (!$link) {
            return [
                'result' => false,
                'subscription_required' => true,
            ];
        }

        return [
            'result' => UpdateRunner::withDownloadLink($link)
                ->silently()
                ->run()
                ->didSucceed(),
            'subscription_required' => false,
        ];
    }
}
