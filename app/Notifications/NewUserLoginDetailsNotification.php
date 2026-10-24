<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserLoginDetailsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $temporaryPassword,
        public readonly ?string $role = null,
        public readonly ?string $propertyName = null,
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/login');

        $mail = (new MailMessage)
            ->subject('Your '.config('app.name').' account')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An account has been created for you on '.config('app.name').'.')
            ->line('Login email: '.$notifiable->email)
            ->line('Temporary password: '.$this->temporaryPassword);

        if ($this->role) {
            $mail->line('Role: '.str($this->role)->replace('_', ' ')->title());
        }

        if ($this->propertyName) {
            $mail->line('Hotel branch: '.$this->propertyName);
        }

        return $mail
            ->action('Sign in', $loginUrl)
            ->line('For security, you will be required to change this temporary password immediately after your first login.')
            ->line('If you were not expecting this account, please contact your hotel administrator.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_user_account',
            'title' => 'Your account is ready',
            'message' => 'Your '.config('app.name').' account has been created. Sign in and change your temporary password.',
            'url' => url('/login'),
            'role' => $this->role,
            'property_name' => $this->propertyName,
        ];
    }
}
