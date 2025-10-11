<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;


class SendOtpNotification extends Notification
{
    use Queueable;

    public $otp;
    public $channel;

    public function __construct($otp, $channel = 'email')
    {
        $this->otp = $otp;
        $this->channel = $channel;
    }

    public function via($notifiable)
    {
        return $this->channel === 'phone' ? ['sms'] : ['mail'];
    }

    public function toSms($notifiable)
    {
        // Use Laravel HTTP client to call Deywuro API
        $response = Http::get(config('services.deywuro_sms.url'), [
            'username'    => config('services.deywuro_sms.username'),
            'password'    => config('services.deywuro_sms.password'),
            'source'      => config('services.deywuro_sms.source'),
            'destination' => $notifiable->phone,
            'message'     => "Your OTP is: {$this->otp}",
        ]);

        if (! $response->successful()) {
            \Log::error('Failed to send OTP SMS', [
                'response' => $response->body(),
            ]);
        }

        return $response->body();
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Verification Code')
            ->line("Your OTP is: {$this->otp}")
            ->line('This code will expire in 15 minutes.');
    }
}
