<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification
{
    use Queueable;

    private string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your GoalDocs Account Credentials')
            ->greeting('Welcome to GoalDocs!')
            ->line('An account has been created for you. Below are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('**Important:** On your first login, you will need to verify your account using OTP (One-Time Password) sent to your email or phone.')
            ->line('Please log in and change your password after completing verification for security.')
            ->action('Login to GoalDocs', url('/login'))
            ->line('If you have any questions, please contact your administrator.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Login credentials sent',
            'email' => $notifiable->email,
        ];
    }
} 