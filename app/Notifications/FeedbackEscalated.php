<?php

namespace App\Notifications;

use App\Models\FeedbackEntry;
use App\Support\Sms\Contracts\SendsSms;
use App\Support\Sms\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackEscalated extends Notification implements ShouldQueue, SendsSms
{
    use Queueable;

    protected FeedbackEntry $feedback;

    public function __construct(FeedbackEntry $feedback)
    {
        $this->feedback = $feedback;
    }

    public function via($notifiable)
    {
        $channels = ['mail'];
        
        // Add SMS if user has mobile number
        if ($notifiable->mobile_number) {
            $channels[] = SmsChannel::class;
        }
        
        return $channels;
    }

    public function toMail($notifiable)
    {
        $qrcode = $this->feedback->qrcode;
        $businessName = $qrcode->data->businessName ?? 'Your Business';
        
        $url = url('/staff/feedback/' . $this->feedback->id);

        return (new MailMessage)
            ->subject('Urgent: Customer Feedback Requires Immediate Attention')
            ->line("A customer feedback has been escalated and requires your immediate attention.")
            ->line("Business: {$businessName}")
            ->line("Rating: {$this->feedback->rating}/5")
            ->line("Urgency: " . ucfirst($this->feedback->urgency))
            ->line("Category: " . ucfirst($this->feedback->category))
            ->when($this->feedback->comment, function ($mail) {
                return $mail->line("Comment: {$this->feedback->comment}");
            })
            ->action('Review Feedback', $url)
            ->line('Please review and respond to this feedback as soon as possible.');
    }

    public function toSms($notifiable): string
    {
        $qrcode = $this->feedback->qrcode;
        $businessName = $qrcode->data->businessName ?? 'Your Business';
        
        return sprintf(
            "URGENT: Customer feedback requires attention. %s - Rating: %d/5, Urgency: %s. Review at: %s",
            $businessName,
            $this->feedback->rating,
            ucfirst($this->feedback->urgency),
            url('/staff/feedback/' . $this->feedback->id)
        );
    }
}
