<?php

namespace App\Support\Security;

use App\Models\Config;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AccountSecurityManager
{
    use WriteLogs;

    private Request $request;

    private int $loginAttemptsToLockTheAccount = 5;

    private int $minutesToResetLoginAttempts = 15;

    private Collection $errors;

    private bool $accountLockOnFailedLoginAttemptsEnabled = false;

    public function __construct(Request $request)
    {
        $this->request = $request;

        if ($number = Config::get('security.login_attempts_to_lock_the_account')) {
            $this->loginAttemptsToLockTheAccount = $number;
        }

        if ($minutes = Config::get('security.minutes_to_reset_login_attempts')) {
            $this->minutesToResetLoginAttempts = $minutes;
        }

        $this->accountLockOnFailedLoginAttemptsEnabled = Config::get('security.account_lock_on_failed_login_attempts_enabled') === 'enabled';

        $this->errors = collect([]);
    }

    public function handleFailedLoginAttempt(Request $request)
    {
        if ($this->accountExists()) {
            if ($this->accountLockOnFailedLoginAttemptsEnabled) {
                $this->protectAccountFromFailedLoginAttempts();
            }
        }

        $this->errors->add($this->getWrongCredentialsErrorMessage());

        $this->throwErrors();

        $this->logWrongLoginAttempt($request);
    }

    private function logWrongLoginAttempt(Request $request) {}

    public function handleLoginAttemptWithWorkingCredentials()
    {
        if ($this->accountLockOnFailedLoginAttemptsEnabled) {
            if ($this->accountIsLocked()) {
                $this->errors->add($this->getAccountLockedErrorMessage());

                $this->throwErrors();
            }
        }
    }

    public function clearAccountLock()
    {
        $this->clearCache($this->accountIsLockedKey());

        $this->clearCache($this->loginAttemptsKey());
    }

    private function protectAccountFromFailedLoginAttempts()
    {
        if ($this->accountIsLocked()) {
            $this->errors->add($this->getAccountLockedErrorMessage());

            $this->throwErrors();
        }

        if ($this->shouldLockAccount()) {
            $this->lockAccount();

            $this->errors->add($this->getAccountLockedErrorMessage());

            $this->throwErrors();
        }

        $this->addLoginAttempt();

        $this->errors->add($this->getLoginAttemptAddedErrorMessage());
    }

    private function getAccountLockedErrorMessage()
    {
        return t('Account is locked, reset your password to be able to login again.');
    }

    private function getWrongCredentialsErrorMessage()
    {
        return t('The provided credentials are incorrect.');
    }

    private function getLoginAttemptAddedErrorMessage()
    {
        return sprintf(
            '%s %s %s',
            t('Account will be locked after'),
            $this->loginAttemptsToLockTheAccount + 1 - $this->getLoginAttempts(),
            t('failed login attempts.')
        );
    }

    private function accountExists()
    {
        return !!User::whereEmail($this->request->email)->first();
    }

    private function shouldLockAccount()
    {
        return $this->getLoginAttempts() >= $this->loginAttemptsToLockTheAccount;
    }

    private function lockAccount()
    {
        $this->putCache($this->accountIsLockedKey(), true);
    }

    private function accountIsLocked()
    {
        return $this->getCache($this->accountIsLockedKey());
    }

    private function accountIsLockedKey()
    {
        return sprintf('accountIsLocked[%s]', $this->request->email);
    }

    private function addLoginAttempt()
    {
        $attempts = $this->getLoginAttempts();

        $this->putCache(
            $this->loginAttemptsKey(),
            $attempts + 1,
            $this->minutesToResetLoginAttempts
        );
    }

    private function getLoginAttempts()
    {
        return $this->getCache($this->loginAttemptsKey());
    }

    private function loginAttemptsKey()
    {
        return sprintf('loginAttempts[%s]', $this->request->email);
    }

    private function throwErrors()
    {
        throw ValidationException::withMessages(
            [
                'email' => $this->errors->all()
            ]
        );
    }

    private function getCache($key)
    {
        return Cache::get($this->cacheKey($key));
    }

    private function putCache($key, $value, $expirationMinutes = null)
    {
        if ($expirationMinutes) {
            $expirationMinutes = now()->addMinutes($expirationMinutes);
        }

        Cache::put($this->cacheKey($key), $value, $expirationMinutes);
    }

    private function clearCache($key)
    {
        Cache::forget($this->cacheKey($key));
    }

    private function cacheKey($key)
    {
        return sprintf('%s::%s', $this::class, $key);
    }
}
