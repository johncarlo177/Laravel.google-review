<?php

namespace App\Notifications;

use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    private Domain $domain;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Domain Has Been Submitted')
            ->line(t('New domain name has been submitted by client. The CNAME record has been configured properly.'))
            ->line(t('Domain: ') . $this->domain->host)
            ->line(t('User: ') . $this->domain->user->name . ' (' . $this->domain->user->email . ').')
            ->action(t('View Domain'), url('/dashboard/domains/edit/' . $this->domain->id))
            ->line(t('Please configure your vHost to serve this domain in order to be able to publish it.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
