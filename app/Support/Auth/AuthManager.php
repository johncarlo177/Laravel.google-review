<?php

namespace App\Support\Auth;

use App\Support\Auth\Workflow\BaseWorkflow;
use App\Support\System\Traits\ClassListLoader;
use App\Support\System\Traits\WriteLogs;

use Throwable;

class AuthManager
{
    use WriteLogs;
    use ClassListLoader;

    private static ?AuthManager $instance = null;

    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new AuthManager();
        }

        return static::$instance;
    }

    public static function boot()
    {
        // Silence is good.

        collect(static::instance()->enabledWorkflows())
            ->each(function (BaseWorkflow $workflow) {
                try {
                    $workflow->boot();
                } catch (Throwable $th) {
                    // 
                }
            });
    }

    public static function emailVerificationEnabled()
    {
        $value = config('app.email_verification_after_sign_up');

        return $value !== 'disabled';
    }

    public static function registerWebRoutes()
    {
        try {
            static::instance()->registerEnabledRoutes();
        } catch (Throwable $th) {
            // If database connection is not present yet, 
            // routes will not be able to register.
        }
    }

    private static function bindSocialiteConfigs()
    {
        $workflows = static::instance()->enabledWorkflows();

        foreach ($workflows as $workflow) {
            $workflow->bindSocialiteConfigs();
        }
    }

    public function registerEnabledRoutes()
    {
        collect(

            $this->enabledWorkflows()

        )->each(function (BaseWorkflow $workflow) {

            $workflow->registerWebRoutes();
        });
    }

    public function enabledWorkflows()
    {
        $filtered = array_filter($this->workflows(), function (BaseWorkflow $workflow) {
            try {
                return $workflow->isEnabled();
            } catch (Throwable $th) {
                return false;
            }
        });

        return array_values($filtered);
    }

    public function getEnabledNames()
    {
        return array_map(function (BaseWorkflow $workflow) {
            return $workflow->name();
        }, $this->enabledWorkflows());
    }

    private function workflows()
    {
        return $this->makeInstances(__DIR__ . '/Workflow');
    }
}
