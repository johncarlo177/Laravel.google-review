<?php

namespace App\Support\Auth\Workflow;

use App\Http\Controllers\AccountController;
use App\Models\Config;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

abstract class BaseWorkflow
{
    use WriteLogs;

    public abstract static function name(): string;

    protected abstract function socialiteConfigs(): array;

    protected function callbackUrl()
    {
        return url(
            sprintf('/auth-workflow/%s/callback', $this->name())
        );
    }

    public function boot()
    {
        $this->bindSocialiteConfigs();
    }

    public function registerWebRoutes()
    {
        Route::prefix('/auth-workflow')->group(function () {
            $route = fn ($path) => sprintf('/%s/%s', $this::name(), $path);

            Route::get(
                $route('redirect'),
                [$this::class, 'renderRedirect']
            );

            Route::get(

                $route('callback'),

                [$this::class, 'renderCallback']

            );
        });
    }

    public function bindSocialiteConfigs()
    {
        foreach ($this->socialiteConfigs() as $name) {
            $this->setLaravelConfig($name, $this->config($name));
        }

        $this->setRedirectConfig();
    }

    protected function setLaravelConfig($key, $value)
    {
        LaravelConfig::set(
            sprintf('services.%s.%s', $this->name(), $key),
            $value
        );
    }

    protected function setRedirectConfig()
    {
        $this->setLaravelConfig('redirect', $this->callbackUrl());
    }

    public function renderRedirect()
    {
        try {
            return Socialite::driver($this->name())->redirect();
        } catch (Throwable $th) {

            $this->logWarning($th->getMessage());

            return response($th->getMessage(), 403)->header(
                'Content-Type',
                'text/plain'
            );
        }
    }

    public function renderCallback()
    {
        $error = null;
        $user = null;
        $token = null;

        try {
            $userArray = (array)Socialite::driver($this::name())->user();

            $user = $this->registerUserIfNeeded(
                email: $this->getEmailFromSocialiteUser($userArray),
                name: $this->getNameFromSocialiteUser($userArray)
            );

            $token = $user->createToken('spa')->plainTextToken;
            //
        } catch (Throwable $th) {
            $error = t('Invalid Auth URL');

            $this->logWarningf('Callback Error: %s', $th->getMessage());

            if (app()->environment('demo')) {
                $error =
                    empty($th->getMessage()) ? t('Invalid Auth URL') : $th->getMessage();
            }
        }

        if ($error) {
            return redirect('/account/login');
        }

        return view('blue.pages.auth-callback', [
            'user' => $user,
            'token' => $token
        ]);
    }

    protected function getEmailFromSocialiteUser($user)
    {
        return $user['email'];
    }

    protected function getNameFromSocialiteUser($user)
    {
        return $user['name'];
    }

    protected function registerUserIfNeeded(string $email, string $name): User
    {
        $user = User::whereEmail($email)->first();

        if (!$user) {
            $user = new User();

            $user->name = $name;

            $user->email = $email;

            $user->email_verified_at = now();

            $user->password = sprintf('auth-workflow-%s', $this->name());

            $user->save();

            event(new Registered($user));
        }

        $user = (new AccountController)->getUser($user->id);

        return $user;
    }

    public function isEnabled()
    {
        return $this->config('enabled') === 'enabled';
    }

    protected function config($name)
    {
        return Config::get(
            $this->getConfigKey($name)
        );
    }

    private function getConfigKey($name)
    {
        return sprintf('auth-workflow.%s.%s', $this::name(), $name);
    }
}
