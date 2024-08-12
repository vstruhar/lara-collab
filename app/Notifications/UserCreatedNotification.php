<?php

namespace App\Notifications;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $password)
    {
        $this->onQueue(Queue::EMAIL->value);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' - Your account was created!')
            ->greeting("{$notifiable->getFirstName()}, welcome aboard!")
            ->line('An account has been set up for you by the administrator. You can click the button below to log in with the provided password. It might be a good idea to change the password when you login.')
            ->line("Password: **{$this->password}**")
            ->action('Login', route('auth.login.form', ['email' => $notifiable->email]))
            ->salutation('See you soon!');
    }
}
