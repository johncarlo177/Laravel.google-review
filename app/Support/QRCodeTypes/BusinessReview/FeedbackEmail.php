<?php

namespace App\Support\QRCodeTypes\BusinessReview;

use App\Models\QRCode;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class FeedbackEmail
{
    private QRCode $qrcode;

    private $feedback = null,
        $stars = null,
        $name = null,
        $mobile = null,
        $email = null;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function withFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    public function withStars($stars)
    {
        $this->stars = $stars;

        return $this;
    }

    private function shouldSend()
    {
        return $this->recepients()->isNotEmpty() && !empty($this->feedback);
    }

    public function send()
    {
        if (!$this->shouldSend()) return;

        $this->recepients()->each(function ($recepient) {
            // 
            Mail::raw(

                $this->body(),

                function (Message $message) use ($recepient) {
                    // 
                    $message->subject($this->subject());

                    $message->to($recepient);
                    // 
                }
            );
        });
    }

    protected function field($name, $value)
    {
        return sprintf('%s: %s', $name, empty($value) ? t('Not submitted.') : $value);
    }

    private function body()
    {
        $message = '';

        $message = implode("\n", [
            $this->field(t('Stars'), $this->stars),
            $this->field(t('Name'), $this->name),
            $this->field(t('Email'), $this->email),
            $this->field(t('Mobile'), $this->mobile),
            $this->field(t('Feedback'), $this->feedback),
        ]);

        $message .= "\n$message";

        return $message;
    }

    private function recepients()
    {
        $raw = $this->design('recepient_email');

        if (empty($raw)) {
            return collect();
        }

        return collect(explode(',', $raw))->map(function ($email) {
            return trim($email);
        })->filter(function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        })->values();
    }

    private function subject()
    {
        return $this->design('email_subject', t('New Feedback Received'));
    }

    private function design($key, $default = null)
    {
        return @$this->qrcode->getWebPageDesign()->value($key, $default);
    }
}
