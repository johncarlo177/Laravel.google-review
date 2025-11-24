<?php

namespace App\Support;

use App\Models\Config;
use App\Models\User;
use App\Plugins\PluginManager;
use App\Rules\AppPassword;
use App\Support\Auth\AuthManager;
use App\Support\Auth\Otp\EmailVerifier;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class RegistrationManager
{
    use WriteLogs;

    private $input = [];

    private ?User $user = null;

    private bool $shouldConfirmPassword = true;

    public static function withInput($input)
    {
        $instance = new static($input);

        return $instance;
    }

    public function withPasswordConfirmation($shouldConfirmPassword)
    {
        $this->shouldConfirmPassword = $shouldConfirmPassword;

        return $this;
    }

    private function __construct($input = [])
    {
        $this->input = $input;
    }

    public function regsiter()
    {
        $this->validateRegistrationEnabled();

        $this->validateInput();

        $this->createUser();

        $this->sendOtpCodeIfNeeded();

        $this->markEmailAsVerifiedIfVerificationIsDisabledInSettings();

        $this->broadcastRegisteredEvent();

        return $this->user;
    }



    private function validateRegistrationEnabled()
    {
        if ($this->isRegistrationDisabled()) {

            $validator = Validator::make([], []);

            $validator->after(function () use ($validator) {
                $validator->errors()->add('email', t('New registrations are disabled.'));
            });

            $validator->validate();
            //
        }
    }

    private function passwordRules()
    {
        $rules = [
            'required',
            'confirmed',
            new AppPassword
        ];

        if (!$this->shouldConfirmPassword) {
            $rules = array_filter($rules, fn($r) => $r != 'confirmed');
        }

        return array_values($rules);
    }

    private function validateInput()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'password' => $this->passwordRules()
        ];

        $validator = Validator::make($this->input, $rules);

        $this->validateEmail($validator);

        MobileNumberManager::extendValidator($validator);

        $validator->validate();
    }

    private function validateEmail(ValidationValidator $validator)
    {
        $user = $this->getExistingUser();

        if ($user?->email_verified_at) {
            $validator->after(function () use ($validator) {
                $validator->errors()->add('email', t('The email is already taken'));
            });
        }
    }

    private function getExistingUser(): ?User
    {
        $email = @$this->input['email'];

        if (!$email) return null;

        $user = User::whereEmail($email)->first();

        return $user;
    }

    private function getNotVerifiedUser(): ?User
    {
        $user = $this->getExistingUser();

        if ($user?->verified_at) {
            return null;
        }

        return $user;
    }

    private function isRegistrationDisabled()
    {
        return Config::get('app.new_user_registration') == 'disabled';
    }

    private function createUser()
    {
        $data = array_merge($this->input, [
            'password' => Hash::make($this->input['password'])
        ]);

        $this->user = $this->getNotVerifiedUser() ?? new User;

        $this->user->fill($data);

        $this->user->save();
    }

    private function markEmailAsVerifiedIfVerificationIsDisabledInSettings()
    {
        if (AuthManager::emailVerificationEnabled()) {
            $this->logInfo('Verification is enabled in settings. User must verify his email.');
            return;
        }

        $this->logInfo('Verification is disabled in settings, so we are marking the email as verified');

        $this->user->markEmailAsVerified();
    }

    protected function sendOtpCodeIfNeeded()
    {
        if (!AuthManager::emailVerificationEnabled()) {
            return;
        }

        EmailVerifier::withEmail($this->user->email)->send();
    }

    private function broadcastRegisteredEvent()
    {
        event(new Registered($this->user));

        PluginManager::doAction(PluginManager::ACTION_ACCOUNT_REGISTERED, $this->user);
    }
}
