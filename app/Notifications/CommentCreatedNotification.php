<?php

namespace App\Notifications;

use App\Enums\Queue;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon>
     */
    public function withDelay(object $notifiable): array
    {
        return [
            'mail' => now()->addMinutes(5),
        ];
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => Queue::EMAIL->value,
        ];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if ($channel === 'mail') {
            return $notifiable
                ->unreadNotifications()
                ->whereJsonContains('data->id', $this->comment->id)
                ->exists();
        }

        return true;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->comment->task->project->name}] {$this->comment->user->name} commented on {$this->comment->task->name} task")
            ->greeting("{$this->comment->user->name} commented on {$this->comment->task->name} task")
            ->line($this->comment->content)
            ->action('Open task', route('projects.tasks.open', ['project' => $this->comment->task->project_id, 'task' => $this->comment->task->id]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->comment->task->id,
            'title' => "{$this->comment->user->name} commented on \"{$this->comment->task->name}\"",
            'subtitle' => "On \"{$this->comment->task->project->name}\" project",
            'link' => route('projects.tasks.open', [$this->comment->task->project_id, $this->comment->task->id]),
            'created_at' => $notifiable->created_at,
            'read_at' => $notifiable->read_at,
        ];
    }
}
