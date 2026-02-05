<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public $booking;
    public $status; // 'submitted', 'approved', 'rejected'

    public function __construct($booking, $status)
    {
        $this->booking = $booking;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        // Kirim email jika statusnya submitted (ke Admin) 
        // ATAU jika user penerima mengaktifkan notif jadwal
        $settings = $notifiable->notification_settings ?? [];

        if ($this->status == 'submitted' || ($settings['notif_jadwal'] ?? true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $room = $this->booking->room->name;
        $date = \Carbon\Carbon::parse($this->booking->booking_date)->format('d M Y');
        $userBooking = $this->booking->user->name; // Nama Dosen yg booking

        $mail = (new MailMessage)->greeting('Halo ' . $notifiable->name . ',');

        // A. Jika Status Submitted (Email untuk Admin)
        if ($this->status == 'submitted') {
            $mail->subject('Permintaan Booking Baru')
                ->line("Dosen **$userBooking** mengajukan booking ruangan **$room**.")
                ->line("Tanggal: $date")
                ->line("Keperluan: " . $this->booking->purpose)
                ->action('Cek Dashboard', url('/dashboard')); // Arahkan ke dashboard admin
        }

        // B. Jika Status Approved (Email untuk Dosen)
        elseif ($this->status == 'approved') {
            $mail->subject('Booking Disetujui')
                ->line("Pengajuan booking ruangan $room pada tanggal $date telah DISETUJUI.")
                ->line("Silakan gunakan ruangan sesuai jadwal.")
                ->action('Lihat Jadwal', url('/jadwal-saya'));
        }

        // C. Jika Status Rejected (Email untuk Dosen)
        else {
            $mail->subject('Booking Ditolak')
                ->line("Pengajuan booking ruangan $room pada tanggal $date DITOLAK.")
                ->line("Alasan: " . $this->booking->rejection_note)
                ->action('Ajukan Ulang', url('/jadwal-saya'));
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        // Data untuk Navbar
        if ($this->status == 'submitted') {
            return [
                'title'   => 'Booking Baru',
                'message' => $this->booking->user->name . " request " . $this->booking->room->name,
                'icon'    => 'bx-calendar-plus',
                'color'   => 'primary',
                'url'     => route(''), // Sesuaikan route dashboard admin
            ];
        } else {
            return [
                'title'   => 'Status Booking',
                'message' => "Booking " . $this->booking->room->name . " " . ($this->status == 'approved' ? 'Disetujui' : 'Ditolak'),
                'icon'    => 'bx-calendar-check',
                'color'   => ($this->status == 'approved' ? 'success' : 'danger'),
                'url'     => route('dashboard.jadwal-saya'),
            ];
        }
    }
}
