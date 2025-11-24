<?php

namespace App\Listeners;

use App\Events\LeadFormResponseReceived;
use App\Models\User;
use App\Notifications\Dynamic\LeadFormResponseNotification;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Throwable;

class OnLeadFormResponseReceived implements ShouldQueue
{
    use WriteLogs;

    protected LeadFormResponseReceived $event;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\LeadFormResponseReceived  $event
     * @return void
     */
    public function handle(LeadFormResponseReceived $event)
    {
        $this->event = $event;

        $this->notifyRecepientIfSpecified();

        $this->notifyWebhooks();
    }

    private function notifyWebhooks()
    {
        $form = $this->event->response->lead_form;

        $urls = @$form->configs['webhooks'];

        if (empty($urls)) {
            return;
        }

        $urls = collect(
            explode("\n", $urls)
        )->filter(
            function ($url) {
                return filter_var(
                    $url,
                    FILTER_VALIDATE_URL
                );
            }
        )
            ->values()
            ->slice(0, 5);

        if ($urls->isEmpty()) {
            return;
        }

        $urls->each(function ($url) {
            try {
                $this->notifyWebhookUrl($url);
            } catch (Throwable $th) {
                $this->logWarning(
                    'Error sending response webhook to %s, %s',
                    $url,
                    $th->getMessage()
                );
            }
        });
    }

    protected function notifyWebhookUrl($url)
    {
        Http::asJson()->post($url, [
            'fields' => $this->event->response->fields,
            'lead_form_id' => $this->event->response->lead_form_id,
            'id' => $this->event->response->id,
        ]);
    }

    private function notifyRecepientIfSpecified()
    {
        $form = $this->event->response->lead_form;

        $email = @$form->configs['recepient_email'];

        if (!$email) return;

        $emails = explode(',', $email);

        foreach ($emails as $email) {

            $email = trim($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $this->logDebug('Sending notification email to recepient %s', $email);

            $user = new User();

            $user->email = $email;

            try {
                $user->notifyNow(
                    LeadFormResponseNotification::instance($this->event->response)
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
    }
}
