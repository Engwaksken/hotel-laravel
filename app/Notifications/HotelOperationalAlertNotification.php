<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HotelOperationalAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $alert)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->alert['subject'] ?? 'Hotel operational alert')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->alert['message'] ?? 'There is a hotel item that needs your attention.');

        if (! empty($this->alert['detail'])) {
            $mail->line($this->alert['detail']);
        }

        if (! empty($this->alert['url'])) {
            $mail->action($this->alert['action'] ?? 'Open HMS', $this->alert['url']);
        }

        return $mail->line('This notification was generated automatically by the Hotel Management System.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->alert['event'] ?? 'operational_alert',
            'title' => $this->alert['subject'] ?? 'Hotel operational alert',
            'message' => $this->alert['message'] ?? '',
            'detail' => $this->alert['detail'] ?? null,
            'url' => $this->alert['url'] ?? url('/'),
            'icon' => $this->alert['icon'] ?? 'fa-bell',
            'subject_type' => $this->alert['subject_type'] ?? null,
            'subject_id' => $this->alert['subject_id'] ?? null,
        ];
    }
}
