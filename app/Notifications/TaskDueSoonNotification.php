<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TaskDueSoonNotification extends Notification {
    use Queueable;
    public function __construct(public array $task) {}
    public function via(object $notifiable): array { return ['mail','database']; }
    public function toMail(object $notifiable): MailMessage {
        return (new MailMessage)->subject('Reminder: task due in 30 minutes')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a reminder that your assigned hotel task is due within 30 minutes.')
            ->line($this->task['title'])
            ->when(!empty($this->task['due_at']), fn($m)=>$m->line('Due: '.$this->task['due_at']))
            ->action('Open task', $this->task['url']);
    }
    public function toArray(object $notifiable): array { return $this->task + ['event'=>'due_soon','icon'=>'fa-clock']; }
}
