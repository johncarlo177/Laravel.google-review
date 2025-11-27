<?php

namespace App\Console\Commands;

use App\Support\Sms\Drivers\Manager;
use Illuminate\Console\Command;

class TestTwilioSms extends Command
{
    protected $signature = 'sms:test-twilio {phone} {message}';

    protected $description = 'Test Twilio SMS by sending a test message';

    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');

        if (empty($phone) || empty($message)) {
            $this->error('Phone number and message are required!');
            $this->line('Usage: php artisan sms:test-twilio +1234567890 "Your test message"');
            return 1;
        }

        $manager = new Manager();
        $twilio = $manager->find('twilio');

        if (!$twilio) {
            $this->error('Twilio driver not found!');
            return 1;
        }

        // Check if enabled
        if (!$twilio->isEnabled()) {
            $this->error('Twilio SMS gateway is not enabled!');
            $this->line('Enable it with: php artisan sms:enable twilio');
            return 1;
        }

        // Check if credentials are configured
        if (empty($twilio->config('account_sid')) || 
            empty($twilio->config('auth_token')) || 
            empty($twilio->config('from_number'))) {
            $this->error('Twilio credentials are not configured!');
            $this->line('Configure it with: php artisan sms:configure-twilio');
            $this->newLine();
            $this->line('Missing:');
            if (empty($twilio->config('account_sid'))) $this->line('  - Account SID');
            if (empty($twilio->config('auth_token'))) $this->line('  - Auth Token');
            if (empty($twilio->config('from_number'))) $this->line('  - From Number');
            return 1;
        }

        $this->info("Sending test SMS to: {$phone}");
        $this->line("Message: {$message}");
        $this->newLine();

        try {
            $result = $twilio->send($phone, $message);

            if ($result) {
                $this->info('✓ SMS sent successfully!');
                $this->line('Message SID: ' . ($result['sid'] ?? 'N/A'));
                return 0;
            } else {
                $this->error('✗ Failed to send SMS. Check logs for details.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

