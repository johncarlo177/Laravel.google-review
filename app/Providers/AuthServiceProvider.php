<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->registerSystemPermissions();

        Gate::define('plugins.manage', function (User $user) {
            return $user->permitted('plugins.manage');
        });
    }

    private function getSystemPermissions()
    {
        return [
            'status',
            'settings',
            'logs',
            'cache',
            'notifications',
            'sms-portals',
            'auth-workflow'
        ];
    }

    private function registerSystemPermissions()
    {
        return collect($this->getSystemPermissions())->each(function ($permission) {
            Gate::define(
                'system.' . $permission,
                function (User $user) use ($permission) {
                    return $user->permitted('system.' . $permission);
                }
            );
        });
    }
}
