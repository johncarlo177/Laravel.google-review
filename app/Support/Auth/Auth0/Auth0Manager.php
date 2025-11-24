<?php

namespace App\Support\Auth\Auth0;

use App\Http\Controllers\AccountController;
use App\Models\Config;
use App\Models\User;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Auth0Manager
{
    public function isEnabled()
    {
        return Config::get('auth0.enabled') === 'enabled';
    }

    private function sdk()
    {
        $configuration = new SdkConfiguration(
            domain: $this->domain(),
            clientId: $this->clientId(),
            clientSecret: $this->clientSecret(),
            cookieSecret: $this->getCookieSecret(),
            redirectUri: url('/auth0/callback'),
            scope: ['openid', 'profile', 'email'],
        );

        $sdk = new Auth0($configuration);

        return $sdk;
    }

    private function domain()
    {
        return Config::get('auth0.domain');
    }

    private function clientId()
    {
        return Config::get('auth0.client_id');
    }

    private function clientSecret()
    {
        return Config::get('auth0.client_secret');
    }

    public static function loginUrl()
    {
        return '/auth0/login';
    }

    public static function logoutUrl()
    {
        return '/auth0/logout';
    }

    public static function callbackUrl()
    {
        return '/auth0/callback';
    }

    public function redirectToAuth0Login()
    {
        return redirect()->to($this->sdk()->login());
    }

    public function redirectToAuth0Logout()
    {
        return redirect()->to(
            $this->sdk()->logout(
                url('/')
            )
        );
    }

    private function getCookieSecret()
    {
        $key = 'auth0.cookie-secret';

        $secret = Config::get($key);

        if (!$secret) {
            $secret = Str::random(32);

            Config::set($key, $secret);
        }

        return $secret;
    }

    public function handleCallback()
    {
        $error = '';
        $hasAuthenticated = isset($_GET['state']) && isset($_GET['code']);
        $hasAuthenticationFailure = isset($_GET['error']);

        // The end user will be returned with ?state and ?code values in their request, when successful.
        if ($hasAuthenticated) {
            try {
                $this->sdk()->exchange();
            } catch (\Throwable $th) {

                Log::error(
                    sprintf(
                        'Unable to complete authentication: %s',
                        $th->getMessage()
                    )
                );

                $error = t('Authentication Error');
            }
        }

        // When authentication was unsuccessful, the end user will be returned with an ?error in their request.
        if ($hasAuthenticationFailure) {
            $error = t('Authentication Error');
            Log::error(sprintf('Authentication failure: %s', htmlspecialchars(strip_tags(filter_input(INPUT_GET, 'error')))));
        }

        if ($error) {
            return view('blue.pages.auth0-callback', compact(
                'error'
            ));
        }


        $user = $this->registerUserIfNeeded();

        $token = $user->createToken('spa')->plainTextToken;

        return view('blue.pages.auth0-callback', compact(
            'user',
            'token'
        ));
    }

    private function registerUserIfNeeded()
    {
        $authUser = $this->sdk()->getUser();

        $email = $authUser['email'];

        $user = User::whereEmail($email)->first();

        if (!$user) {
            $user = new User();

            $user->name = $authUser['name'];

            $user->email = $email;

            $user->email_verified_at = now();

            $user->password = 'auth0';

            $user->save();

            event(new Registered($user));
        }

        $user = (new AccountController)->getUser($user->id);

        return $user;
    }
}
