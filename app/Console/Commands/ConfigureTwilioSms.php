<?php

namespace App\Console\Commands;

use App\Support\Sms\Drivers\Manager;
use Illuminate\Console\Command;

class ConfigureTwilioSms extends Command
{
    protected $signature = 'sms:configure-twilio 
                            {--account-sid= : Twilio Account SID}
                            {--auth-token= : Twilio Auth Token}
                            {--from-number= : Twilio Phone Number (E.164 format, e.g., +1234567890)}
                            {--enable : Enable Twilio after configuration}';

    protected $description = 'Configure Twilio SMS gateway credentials';

    public function handle()
    {
        $manager = new Manager();
        $twilio = $manager->find('twilio');

        if (!$twilio) {
            $this->error('Twilio driver not found!');
            return 1;
        }

        $this->info('Twilio SMS Configuration');
        $this->line('=======================');
        $this->newLine();

        // Get current values
        $currentAccountSid = $twilio->config('account_sid');
        $currentAuthToken = $twilio->config('auth_token');
        $currentFromNumber = $twilio->config('from_number');
        $isEnabled = $twilio->isEnabled();

        // Account SID
        $accountSid = $this->option('account-sid') 
            ?: $this->ask('Twilio Account SID', $currentAccountSid);
        
        if (empty($accountSid)) {
            $this->error('Account SID is required!');
            return 1;
        }

        // Auth Token
        if ($this->option('auth-token')) {
            $authToken = $this->option('auth-token');
        } else {
            if ($currentAuthToken) {
                $this->line('Current Auth Token: ' . str_repeat('*', min(strlen($currentAuthToken), 20)));
                if (!$this->confirm('Do you want to change the Auth Token?', false)) {
                    $authToken = $currentAuthToken;
                } else {
                    $authToken = $this->secret('Twilio Auth Token');
                }
            } else {
                $authToken = $this->secret('Twilio Auth Token');
            }
        }
        
        if (empty($authToken)) {
            $this->error('Auth Token is required!');
            return 1;
        }

        // From Number
        $fromNumber = $this->option('from-number') 
            ?: $this->ask('Twilio Phone Number (E.164 format, e.g., +1234567890)', $currentFromNumber);
        
        if (empty($fromNumber)) {
            $this->error('From Number is required!');
            return 1;
        }

        // Validate E.164 format
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $fromNumber)) {
            if ($this->confirm('The phone number doesn\'t appear to be in E.164 format (+1234567890). Continue anyway?', true)) {
                // Continue
            } else {
                return 1;
            }
        }

        // Save configuration
        $twilio->config(['account_sid' => $accountSid]);
        $twilio->config(['auth_token' => $authToken]);
        $twilio->config(['from_number' => $fromNumber]);

        // Clear config cache to ensure changes are reflected immediately
        \App\Models\Config::clearCache();

        $this->info('✓ Configuration saved!');
        $this->newLine();

        // Enable if requested
        if ($this->option('enable') || $this->confirm('Enable Twilio SMS gateway?', true)) {
            $twilio->enable();
            \App\Models\Config::clearCache(); // Clear cache after enabling
            
            // Verify
            $manager = new Manager();
            $twilio = $manager->find('twilio');
            if ($twilio->isEnabled()) {
                $this->info('✓ Twilio SMS gateway enabled and verified!');
            } else {
                $this->warn('⚠ Configuration saved but enable status may need cache clear.');
            }
        } else {
            $this->warn('Twilio SMS gateway is configured but not enabled.');
            $this->line('Enable it later with: php artisan sms:enable twilio');
        }

        $this->newLine();
        $this->info('Configuration complete!');
        $this->line('Test it with: php artisan sms:test-twilio +1234567890 "Test message"');

        return 0;
    }
}

