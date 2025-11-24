<?php

namespace App\Support\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Wrapper around PHP Mailer
 */
class Mailer extends PHPMailer
{
    public function __construct()
    {
        parent::__construct(true);

        $this->SMTPDebug = SMTP::DEBUG_LOWLEVEL;

        $this->isSMTP();
        $this->Host       = config('mail.mailers.smtp.host');

        $this->SMTPAuth   = config('mail.mailers.smtp.auth_type') != 'none';

        $this->Username   = config('mail.mailers.smtp.username');
        $this->Password   = config('mail.mailers.smtp.password');

        $this->Port       = config('mail.mailers.smtp.port');

        $this->setTimeout();

        $this->CharSet = 'UTF-8';

        $this->setAuthType();

        if ($e = $this->getEncryption()) {
            $this->SMTPSecure = $e;
        }

        $this->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        //Recipients
        $this->setFrom(
            config('mail.from.address'),
            config('mail.from.name')
        );
    }

    private function verifyEncryption($enc)
    {
        $found = array_search(
            $enc,
            [PHPMailer::ENCRYPTION_SMTPS, PHPMailer::ENCRYPTION_STARTTLS]
        ) !== false;

        return $enc && $found;
    }

    private function getEncryption()
    {
        $enc = config('mail.mailers.smtp.encryption');

        $valid = $this->verifyEncryption($enc);

        if (!$valid) return null;

        return $enc;
    }

    private function setAuthType()
    {
        $type = config('mail.mailers.smtp.auth_type');

        if (!$type || $type === 'none') {
            return;
        }

        $this->AuthType = $type;
    }

    private function setTimeout()
    {
        $timeout = config('mail.mailers.smtp.timeout');

        if (empty($timeout)) return;

        $this->Timeout = $timeout;

        $this->getSMTPInstance()->Timelimit = $timeout;
    }
}
