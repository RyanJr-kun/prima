<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceLogin extends Notification
{
    use Queueable;

    private $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['mail']; // Kirim via Email
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Peringatan Keamanan: Login dari Perangkat Baru')
            ->greeting('Halo ' . $notifiable->name)
            ->line('Kami mendeteksi login baru ke akun Anda dari perangkat yang tidak dikenali.')
            ->line('**IP Address:** ' . $this->details['ip'])
            ->line('**Browser:** ' . $this->details['browser'])
            ->line('**Waktu:** ' . $this->details['time'])
            ->line('Jika ini adalah Anda, silakan abaikan pesan ini.')
            ->line('Jika bukan Anda, segera ganti password Anda!')
            ->action('Ganti Password', url('/change-password')); // Sesuaikan route ganti password
    }
}
