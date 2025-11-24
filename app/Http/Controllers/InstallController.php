<?php

namespace App\Http\Controllers;

use App\Console\Commands\AppInstall;
use App\Events\ConfigChanged;
use App\Interfaces\EnvSaver;
use App\Support\SoftwareUpdate\DatabaseUpdateManager;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InstallController extends Controller
{
    private $env;

    public function __construct(EnvSaver $env)
    {
        $this->env = $env;

        if (config('app.installed')) {
            if (!app()->runningInConsole() && !str_starts_with(request()->path(), 'docs/api')) {
                abort(403);
            }
        }
    }

    public function saveEnvVariables(Request $request)
    {
        try {
            $this->env->saveMany($request->all());

            return $this->env->load($request->keys());
            // 
        } catch (Throwable $th) {
            // 
            Log::error($th->getMessage());
        }
    }

    public function loadEnvVariables(Request $request)
    {
        return $this->env->load($request->keys());
    }

    public function verifyMail(Request $request)
    {
        try {
            Mail::raw('This is test email, to confirm the mail credentials are working', function ($msg) {
                $msg->to(config('app.superuser.email'))->subject('Test Email');
            });

            return [
                'pass' => true
            ];
        } catch (\Throwable $ex) {
            return [
                'pass' => false
            ];
        }
    }

    public function verifyDatabase()
    {
        try {
            DB::statement('select * from information_schema.TABLES', []);

            return [
                'pass' => true
            ];
        } catch (\Throwable $ex) {
            return [
                'pass' => false,
                'error' => $ex->getMessage()
            ];
        }
    }

    public function verifyPurchaseCode(Request $request)
    {
        $purchaseCode = config('app.purchase_code');

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $version = $composer['version'];

        $name = $composer['name'];

        $http = Http::acceptJson()->timeout(20);

        if (!config('app.http_client_verify_ssl')) {
            $http->withoutVerifying();
        }

        $marketplace = config('app.marketplace');

        $url = url('/');

        $response = $http->get(
            'https://quickcode.digital/api/verify/' . $purchaseCode,
            compact('name', 'version', 'marketplace', 'url')
        );

        return [
            'pass' => @$response['verified'] ?? false,
            'message' => $response['message'],
        ];
    }

    protected function prepareLongOperation()
    {
        @ini_set('display_errors', 1);
        @ini_set('display_startup_errors', 1);
        @ini_set('memory_limit', '400M');
        error_reporting(E_ALL);
        set_time_limit(0);
        ignore_user_abort(true);
    }

    public function migrateDatabase()
    {
        $this->prepareLongOperation();

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $th) {
        }

        return ['result' => 'ok'];
    }

    public function completeInstallation(Request $request)
    {
        $this->prepareLongOperation();

        $this->env->save('APP_URL', url('/'));

        $this->env->save('APP_HOST', $request->getHttpHost());

        $this->env->save('APP_PORT', $_SERVER['SERVER_PORT']);

        $this->env->save('FRONTEND_URL', url('/'));

        $this->env->save('APP_INSTALLED', 'true');

        $this->env->save('APP_ENV', 'production');

        $this->env->save('APP_DEBUG', 'false');

        $this->env->save('LOG_LEVEL', 'info');

        Artisan::call('key:generate');

        try {
            $seeder = new DatabaseSeeder();

            $seeder->seedProduction();

            Log::info('Database seeded successfully');
        } catch (\Throwable $th) {
            Log::error(
                'Database seeder failed',
                ['message' => $th->getMessage()]
            );
        }

        (new AppInstall)->attemptCronJobInstallation();

        /** @var \App\Support\SoftwareUpdate\DatabaseUpdateManager */
        $databaseManager = app(DatabaseUpdateManager::class);

        $databaseManager->updateDatabaseIfNeeded();

        ConfigChanged::fire(
            [
                'app.name',
                'frontend.slogan'
            ]
        );

        return [
            'pass' => true
        ];
    }
}
