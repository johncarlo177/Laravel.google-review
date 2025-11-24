<?php

namespace App\Support\CustomForms\ResponseHandlers;

use App\Models\User;
use App\Notifications\Dynamic\CustomFormResponseNotification;
use App\Support\System\Traits\WriteLogs;
use Throwable;

/**
 * Handle the response when the form is used within QR Code forms, 
 * the end user is not required to be signed in to 
 * submit the form in this case.
 * 
 * Example: it is used in automatic form popup in BioLinks and vCards,
 * 
 * Applicable usecases: new dynamic type, new biolinks block.
 */


class QRCodeResponseHandler extends BaseResponseHandler
{
    use WriteLogs;

    public function type()
    {
        return 'qrcode.*';
    }

    protected function handle()
    {
        if ($this->getEmailRecepients()->isEmpty()) {
            return;
        }

        $this->getEmailRecepients()->each(function ($email) {
            return $this->notify($email);
        });
    }

    private function notify($email)
    {
        $user = new User();

        $user->email = $email;

        try {
            $user->notifyNow(
                CustomFormResponseNotification::instance($this->response)
            );
            // 
            $this->logDebug('email sent to %s', $email);
            // 
        } catch (Throwable $th) {
            $this->logWarning(
                'Cannot send lead form notification email to (%s), failed with message: %s',
                $email,
                $th->getMessage()
            );
        }
    }

    private function getEmailRecepients()
    {
        $raw = $this->form->getSettings('recepient_email');

        if (!$raw) {
            return collect();
        }

        return collect(explode(',', $raw))->map(function ($email) {
            return trim($email);
        })->filter(function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        })->values();
    }
}
