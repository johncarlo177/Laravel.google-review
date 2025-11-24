<?php

namespace App\Http\Controllers;

use App\Interfaces\SubscriptionManager;
use App\Interfaces\UserManager;

use App\Models\User;

use App\Rules\AppPassword;
use App\Support\AI\UsageManager;
use App\Support\Auth\Otp\EmailVerifier;
use App\Support\Billing\AccountCreditBillingManager;
use App\Support\Billing\BillingManager;
use Illuminate\Http\Request;
use App\Support\RegistrationManager;
use App\Support\Security\AccountSecurityManager;
use App\Support\System\Traits\WriteLogs;
use App\Support\WelcomeNotifier;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Events\PasswordReset;

use Illuminate\Support\Facades\URL;

class AccountController extends Controller
{
    use WriteLogs;

    private AccountCreditBillingManager $accountCredit;

    private BillingManager $billing;

    private UserManager $users;

    private SubscriptionManager $subscriptions;

    public function __construct()
    {
        $this->accountCredit = app(AccountCreditBillingManager::class);
        $this->billing = app(BillingManager::class);
        $this->users = app(UserManager::class);
        $this->subscriptions  = app(SubscriptionManager::class);
    }

    public function register(Request $request)
    {
        $user = RegistrationManager::withInput($request->all())
            ->regsiter();

        return [
            'token' => $user->createToken('spa')->plainTextToken
        ];
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return [
            'sent' => true
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $securityManager = new AccountSecurityManager($request);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $securityManager->handleFailedLoginAttempt($request);
        }

        if (!$user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => [t('You must verify your email before login')]
            ]);
        }

        $securityManager->handleLoginAttemptWithWorkingCredentials();

        $user = $this->getUser(
            $user->id
        );

        return [
            'token' => $user->createToken('spa')->plainTextToken,
            'user' => $user
        ];
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            abort(403, 'Invalid user');
        }

        if (!hash_equals(
            (string) $request->route('hash'),
            sha1($user->getEmailForVerification())
        )) {
            abort(403, 'Invalid hash code');
        }

        if (!$user->hasVerifiedEmail()) {

            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return redirect(
            config('frontend.url') . '/account/email-verified'
        );
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            $this->logWarning(
                'Invalid password reset has been attempted. [email = %s] [ip = %s]',
                $request->input('email'),
                $request->ip()
            );
        }

        ResetPassword::createUrlUsing(

            function ($notifiable, $token) {

                $email = $notifiable->getEmailForPasswordReset();

                return config('frontend.url')
                    . "/account/reset-password?token=$token&email=$email";
            }
        );

        Password::sendResetLink(
            $request->only('email')
        );

        return [
            'success' => 1
        ];
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', new AppPassword],
        ]);


        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {

                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                $securityManager = new AccountSecurityManager($request);

                $securityManager->clearAccountLock();

                event(new PasswordReset($user));
            }
        );

        return compact('status');
    }

    public function myself(Request $request)
    {
        return $this->getUser($request->user()->id);
    }

    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        $subscription = $this->users->getCurrentSubscription($user);

        $this->subscriptions->cancelSubscription($subscription);

        return $subscription;
    }

    public function getUser($userId)
    {
        $user = User::with(
            'subscriptions',
            'subscriptions.subscription_plan',
            'subscriptions.statuses',
            'parent_user.subscriptions',
            'parent_user.subscriptions.subscription_plan',
            'parent_user.subscriptions.statuses',
        )->find(
            $userId
        );

        if ($this->billing->isAccountCreditBilling()) {
            $user->account_balance = $this->accountCredit->forUser($user)->getAccountBalance();
        }

        $user->used_ai_generations = UsageManager::forUser($user)->getUsage();

        $user->profile_image_url = $user->getProfileImageSrc();

        return $user;
    }

    public function actAs(User $user, Request $request)
    {
        if (!$request->user()->permitted('user.list-all')) {
            abort(403);
        }

        $user = $this->getUser($user->id);

        return [
            'token' => $user->createToken('spa')->plainTextToken,
            'user' => $user
        ];
    }

    public function generateMagicLoginUrl(User $user, Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $date = now()->addHours(24);

        $singedUrl = URL::temporarySignedRoute(
            name: 'magic-login',
            parameters: [
                'user' => $user,
            ],
            expiration: $date,
            absolute: false
        );

        return [
            'url' => url($singedUrl)
        ];
    }

    public function magicLogin(User $user, Request $request)
    {
        if (!URL::hasValidRelativeSignature($request)) {
            return abort(403, 'Invalid signature');
        }

        return view(
            'blue.pages.auth-callback',
            [
                'user' => $this->getUser($user->id),
                'token' => $user->createToken('spa')->plainTextToken
            ]
        );
    }

    public function isEmailFound(Request $request)
    {
        $user = User::where([
            'email' => $request->input('email'),
        ])
            ->whereNotNull('email_verified_at')
            ->first();

        return [
            'found' => !empty($user)
        ];
    }

    public function sendOtpCode(Request $request)
    {
        EmailVerifier::withEmail(
            $request->input('email')
        )->send();

        return [
            'sent' => true
        ];
    }

    public function verifyOtpCode(Request $request)
    {
        $success = EmailVerifier::withEmail(
            $request->input('email')
        )
            ->withOtp($request->input('otp'))
            ->verify();

        $user = User::whereEmail($request->input('email'))->first();

        if ($user) {
            WelcomeNotifier::withUser($user)
                ->onOtpVerification()
                ->notifyIfNeeded();
        }

        return [
            'result' => $success
        ];
    }

    public function otpRegistration(Request $request)
    {
        $email = $request->input('email');

        if (!EmailVerifier::withEmail($email)->isRecentlyVerified()) {
            abort(401);
        }

        $user = RegistrationManager::withInput($request->all())
            ->withPasswordConfirmation(false)
            ->regsiter();

        $user->email_verified_at = now();

        $user->save();

        $user = User::whereEmail($request->input('email'))->first();

        WelcomeNotifier::withUser($user)
            ->onOtpVerification()
            ->notifyIfNeeded();

        return [
            'token' => $user->createToken('spa')->plainTextToken,
            'user' => $user
        ];
    }
}
