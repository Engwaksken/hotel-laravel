<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TaskAssignedNotification extends Notification {
    use Queueable;
    public function __construct(public array $task) {}
    public function via(object $notifiable): array { return ['mail','database']; }
    public function toMail(object $notifiable): MailMessage {
        return (new MailMessage)->subject('New task assigned: '.$this->task['title'])
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A hotel task has been assigned to you.')
            ->line($this->task['title'])
            ->when(!empty($this->task['due_at']), fn($m)=>$m->line('Due: '.$this->task['due_at']))
            ->action('Open task', $this->task['url'])
            ->line('Please review the task and update its status when you start or complete it.');
    }
    public function toArray(object $notifiable): array { return $this->task + ['event'=>'assigned','icon'=>'fa-list-check']; }
}
