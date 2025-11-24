<?php

namespace App\Support\Mail;

use Exception;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PHPMailerTransport extends AbstractTransport
{
    private PHPMailer $mailer;

    protected function doSend(SentMessage $message): void
    {
        $this->mailer = new Mailer;

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $this->mailer->Subject = $email->getSubject();

        foreach ($email->getTo() as $recipient) {
            $this->mailer->addAddress($recipient->getAddress(), $recipient->getName());
        }

        foreach ($email->getCc() as $cc) {
            $this->mailer->addCC($cc->getAddress(), $cc->getName());
        }

        foreach ($email->getBcc() as $bcc) {
            $this->mailer->addBCC($bcc->getAddress(), $bcc->getName());
        }

        $this->mailer->isHTML(false);

        $this->mailer->Body = $email->getTextBody();

        if ($email->getHtmlBody()) {
            $this->mailer->isHTML(true);
            $this->mailer->Body = $email->getHtmlBody();
        }

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();

            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

            $this->mailer->addStringAttachment($attachment->getBody(), $filename);
        }

        ob_start();

        try {
            $this->mailer->send();
        } catch (\Throwable $th) {
            $message->appendDebug($th->getMessage() . PHP_EOL . PHP_EOL);
        }

        $debug = ob_get_clean();

        $message->appendDebug($debug);
    }

    public function __toString(): string
    {
        return sprintf('phpmailer');
    }
}
