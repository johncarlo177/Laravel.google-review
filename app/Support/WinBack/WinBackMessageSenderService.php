<?php

namespace App\Support\WinBack;

use App\Models\WinBackMessage;
use App\Models\WinBackCustomer;
use App\Support\Sms\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WinBackMessageSenderService
{
    protected SmsService $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    /**
     * Send a win-back message
     */
    public function send(WinBackMessage $message): bool
    {
        $customer = $message->customer;
        $channel = $message->channel;

        try {
            switch ($channel) {
                case WinBackMessage::CHANNEL_SMS:
                    return $this->sendSms($message, $customer);
                
                case WinBackMessage::CHANNEL_EMAIL:
                    return $this->sendEmail($message, $customer);
                
                case WinBackMessage::CHANNEL_WHATSAPP:
                    // TODO: Implement WhatsApp integration
                    return $this->sendSms($message, $customer); // Fallback to SMS
                
                default:
                    Log::warning("Unsupported channel: {$channel}");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Message send error: ' . $e->getMessage());
            $message->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS message
     */
    protected function sendSms(WinBackMessage $message, WinBackCustomer $customer): bool
    {
        if (!$customer->phone) {
            $message->markAsFailed('No phone number available');
            return false;
        }

        if (!$this->smsService->isAvailable()) {
            $message->markAsFailed('SMS service not configured');
            return false;
        }

        $result = $this->smsService->send($customer->phone, $message->message_content);

        if ($result) {
            $message->markAsSent($result['sid'] ?? null);
            $customer->update(['last_contacted_at' => now()]);
            return true;
        }

        $message->markAsFailed('SMS send failed');
        return false;
    }

    /**
     * Send email message
     */
    protected function sendEmail(WinBackMessage $message, WinBackCustomer $customer): bool
    {
        if (!$customer->email) {
            $message->markAsFailed('No email address available');
            return false;
        }

        try {
            Mail::raw($message->message_content, function ($mail) use ($customer, $message) {
                $mail->to($customer->email, $customer->name)
                    ->subject('We miss you!');
            });

            $message->markAsSent();
            $customer->update(['last_contacted_at' => now()]);
            return true;
        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Determine best channel for customer
     */
    public function determineChannel(WinBackCustomer $customer, ?string $preferred = null): string
    {
        if ($preferred) {
            return $preferred;
        }

        // Prefer SMS if phone available and SMS service is configured
        if ($customer->phone && $this->smsService->isAvailable()) {
            return WinBackMessage::CHANNEL_SMS;
        }

        // Fallback to email
        if ($customer->email) {
            return WinBackMessage::CHANNEL_EMAIL;
        }

        // Default to email
        return WinBackMessage::CHANNEL_EMAIL;
    }
}

