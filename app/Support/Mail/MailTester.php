<?php

namespace App\Support\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailTester
{
    private $message = 'We have sent this email successfully using SMTP protocol.';

    private $to = 'mohammad.a.alhomsi@gmail.com';

    private $subject = 'SMTP Test';

    private $debugLog;

    public function __construct($to = null, $message = null, $subject = null)
    {
        if ($to) {
            $this->to = $to;
        }

        if ($message) {
            $this->message = $message;
        }

        if ($subject) {
            $this->subject = $subject;
        }
    }

    public function run()
    {
        $message = Mail::raw(
            $this->message,
            function ($message) {
                $message->to($this->to);
                $message->subject($this->subject);
            }
        );

        $this->debugLog = $message->getSymfonySentMessage()->getDebug();

        return $this;
    }

    public function getDebugLog()
    {
        return $this->debugLog;
    }
}
