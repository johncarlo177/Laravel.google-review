<?php

namespace App\Support\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Twilio extends BaseDriver
{
    private ?string $accountSid = null;
    private ?string $authToken = null;
    private ?string $fromNumber = null;

    public function slug()
    {
        return 'twilio';
    }

    public function doSend(string $to, string $text)
    {
        if (!$this->credentialsArePresent()) {
            Log::warning('Twilio credentials not configured');
            return null;
        }

        try {
            $url = sprintf(
                'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json',
                $this->accountSid
            );

            $response = Http::asForm()
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->post($url, [
                    'From' => $this->fromNumber,
                    'To' => $this->formatPhoneNumber($to),
                    'Body' => $text,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Twilio SMS sent successfully', ['sid' => $data['sid'] ?? null]);
                return $data;
            } else {
                Log::error('Twilio SMS failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage());
            return null;
        }
    }

    private function credentialsArePresent()
    {
        return !empty($this->config('account_sid')) &&
               !empty($this->config('auth_token')) &&
               !empty($this->config('from_number'));
    }

    public function __construct()
    {
        if ($this->credentialsArePresent()) {
            $this->accountSid = $this->config('account_sid');
            $this->authToken = $this->config('auth_token');
            $this->fromNumber = $this->config('from_number');
        }
    }

    /**
     * Format phone number for Twilio (E.164 format)
     */
    private function formatPhoneNumber(string $number): string
    {
        // Remove all non-numeric characters except +
        $number = preg_replace('/[^0-9+]/', '', $number);

        // If number doesn't start with +, assume it needs country code
        // This is a basic implementation - you may want to enhance this
        if (!str_starts_with($number, '+')) {
            // If it starts with 0, remove it (common in some countries)
            if (str_starts_with($number, '0')) {
                $number = substr($number, 1);
            }
            // Add +1 for US/Canada if not present (you may want to make this configurable)
            if (strlen($number) == 10) {
                $number = '+1' . $number;
            }
        }

        return $number;
    }
}

