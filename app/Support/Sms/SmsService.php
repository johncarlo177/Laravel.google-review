<?php

namespace App\Support\Sms;

use App\Support\Sms\Drivers\Manager;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected Manager $manager;

    public function __construct()
    {
        $this->manager = new Manager();
    }

    /**
     * Send SMS to a phone number
     *
     * @param string $to Phone number in E.164 format (e.g., +1234567890)
     * @param string $message Message text
     * @return bool|array Returns false on failure, or array with message details on success
     */
    public function send(string $to, string $message)
    {
        $driver = $this->manager->enabledDriver();

        if (!$driver) {
            Log::warning('No SMS driver enabled. SMS not sent.', ['to' => $to]);
            return false;
        }

        try {
            $result = $driver->send($to, $message);
            
            if ($result) {
                Log::info('SMS sent successfully', [
                    'to' => $to,
                    'driver' => $driver->slug(),
                    'sid' => $result['sid'] ?? null,
                ]);
                return $result;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SMS sending error: ' . $e->getMessage(), [
                'to' => $to,
                'driver' => $driver->slug(),
            ]);
            return false;
        }
    }

    /**
     * Check if SMS is configured and enabled
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $driver = $this->manager->enabledDriver();
        return $driver !== null;
    }

    /**
     * Get the enabled SMS driver
     *
     * @return \App\Support\Sms\Drivers\BaseDriver|null
     */
    public function getDriver()
    {
        return $this->manager->enabledDriver();
    }
}

