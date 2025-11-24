<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Notifications\Dynamic\SubscriptionExpiringSoon;
use App\Notifications\Dynamic\Base as BaseNotification;
use App\Notifications\Dynamic\BulkOperationCompleted;
use App\Notifications\Dynamic\CustomFormResponseNotification;
use App\Notifications\Dynamic\DynamicQRCodeLimitReached;
use App\Notifications\Dynamic\InviteUser;
use App\Notifications\Dynamic\LeadFormResponseNotification;
use App\Notifications\Dynamic\OTPNotification;
use App\Notifications\Dynamic\ScanLimitReached;
use App\Notifications\Dynamic\SubscriptionExpired;
use App\Notifications\Dynamic\TrialExpired;
use App\Notifications\Dynamic\WelcomeAdmin;
use App\Notifications\Dynamic\WelcomeCustomer;

class NotificationsSeeder extends Seeder
{
    /** @var string software_version/seeder_version */
    protected $version = 'v2.134/4';

    protected function run()
    {
        $this->seedNotification(
            classString: TrialExpired::class,
            emailSubject: 'Your trial subscription is expired',
            emailBody: '# Hello,

Your trial subscription has been expired.

Your dynamic QR codes will be disabled.

You can upgrade your subscription from [here](PLANS_PAGE_URL).',
            smsBody: 'Your trial subscription has been expired. To renew, please open the following URL:

PLANS_PAGE_URL'
        );

        $this->seedNotification(
            classString: SubscriptionExpiringSoon::class,
            emailSubject: 'Your subscription is about to expire',
            emailBody: '# Hello,

Your subscription is about to expire.

Your dynamic QR codes will be disabled after subscription expiration.

You can renew your subscription from [here](PLANS_PAGE_URL).',
            smsBody: 'Your subscription is about to expire. To renew, please open the following URL:

PLANS_PAGE_URL',
            extra: [
                'remaining_days' => 3
            ]
        );

        $this->seedNotification(
            classString: SubscriptionExpired::class,
            emailSubject: 'Your subscription is expired',
            emailBody: '# Hello,

Your subscription has been expired.

Your dynamic QR codes will be disabled.

You can renew your subscription from [here](PLANS_PAGE_URL).',
            smsBody: 'Your subscription has been expired. To renew, please open the following URL:

PLANS_PAGE_URL'
        );

        $this->seedNotification(
            classString: DynamicQRCodeLimitReached::class,
            emailSubject: 'Dynamic QR codes limit reached',
            emailBody: '# Hello,

You can no longer create new dynamic QR codes with your current subscription,

You can upgrade your subscription from [here](PLANS_PAGE_URL).
            ',
            smsBody: 'You can no longer create new dynamic QR codes with your current subscription

Upgrade your subcription: PLANS_PAGE_URL
'
        );


        $this->seedNotification(
            classString: ScanLimitReached::class,
            emailSubject: 'Scan limit reached',
            emailBody: '# Hello,

Your dynamic QR codes cannot recieve more scans with your current subscription,

You can upgrade your subscription from [here](PLANS_PAGE_URL).
            ',
            smsBody: 'Your dynamic QR codes cannot recieve more scans with your current subscription,

Upgrade your subcription: PLANS_PAGE_URL
'
        );

        $this->seedDefaults(InviteUser::class);

        $this->seedDefaults(BulkOperationCompleted::class);

        $this->seedDefaults(LeadFormResponseNotification::class);

        $this->seedDefaults(CustomFormResponseNotification::class);

        $this->seedDefaults(OTPNotification::class);

        $this->seedDefaults(WelcomeCustomer::class);

        $this->seedDefaults(WelcomeAdmin::class);
    }

    private function seedDefaults(string $classString, $extra = [])
    {
        /** @var BaseNotification */
        $instance = new $classString;

        $this->setConfigIfNeeded($instance, 'enabled', true);

        $this->setConfigIfNeeded($instance, 'email_subject', $instance->defaultEmailSubject());

        $this->setConfigIfNeeded($instance, 'email_body', $instance->defaultEmailBody());

        $this->setConfigIfNeeded($instance, 'sms_body', $instance->defaultSmsBody());

        foreach ($extra as $key => $value) {
            $this->setConfigIfNeeded($instance, $key, $value);
        }
    }

    private function seedNotification(string $classString, $emailSubject, $emailBody, $smsBody, $extra = [])
    {
        /** @var BaseNotification */
        $instance = new $classString;

        $this->setConfigIfNeeded($instance, 'enabled', true);

        $this->setConfigIfNeeded($instance, 'email_subject', $emailSubject);

        $this->setConfigIfNeeded($instance, 'email_body', $emailBody);

        $this->setConfigIfNeeded($instance, 'sms_body', $smsBody);

        foreach ($extra as $key => $value) {
            $this->setConfigIfNeeded($instance, $key, $value);
        }
    }

    private function setConfigIfNeeded(BaseNotification $instance, $key, $value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_bool($value)) {
            if (!is_bool($instance->config($key))) {
                return $instance->config([$key => $value]);
            }
        }

        if (empty($instance->config($key))) {
            $instance->config([$key => $value]);
        }
    }
}
