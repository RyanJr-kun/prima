<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentActionNotification extends Notification
{
    use Queueable;

    public $document;
    public $action; // 'submitted', 'approved', 'rejected'
    public $actorName; // Siapa yang melakukan aksi

    public function __construct($document, $action, $actorName)
    {
        $this->document = $document;
        $this->action = $action;
        $this->actorName = $actorName;
    }

    public function via($notifiable)
    {
        // Default: Database (Navbar)
        $channels = ['database'];

        // Cek Settingan User (Email)
        $settings = $notifiable->notification_settings;

        // Jika Reject/Approve, cek setting 'notif_approval'
        if (in_array($this->action, ['approved', 'rejected'])) {
            if ($settings['notif_approval'] ?? true) {
                $channels[] = 'mail';
            }
        }

        // Jika Submitted (Request baru), anggap penting (kirim email)
        if ($this->action == 'submitted') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $docType = ucwords(str_replace('_', ' ', $this->document->type));
        $status = strtoupper(str_replace('_', ' ', $this->document->status));
        $url = url('/documents'); // Sesuaikan route index dokumen

        $mail = (new MailMessage)->greeting('Halo ' . $notifiable->name . ',');

        if ($this->action == 'submitted') {
            $mail->subject("Permintaan Approval Baru: $docType")
                ->line("$this->actorName baru saja mengajukan dokumen $docType.")
                ->line("Mohon segera diperiksa.");
        } elseif ($this->action == 'approved') {
            $mail->subject("Dokumen Disetujui: $docType")
                ->line("Dokumen $docType Anda telah DISETUJUI oleh $this->actorName.")
                ->line("Status saat ini: $status");
        } elseif ($this->action == 'rejected') {
            $mail->subject("REVISI Dokumen: $docType")
                ->line("Dokumen $docType Anda DIKEMBALIKAN oleh $this->actorName.")
                ->line("Catatan: " . $this->document->feedback_message)
                ->line("Silakan perbaiki dan ajukan ulang.");
        }

        return $mail->action('Lihat Dokumen', $url);
    }

    public function toArray($notifiable)
    {
        // Data untuk disimpan di tabel notifications (JSON)
        // Kita sesuaikan format untuk template Sneat
        $title = '';
        $msg = '';
        $icon = '';
        $color = '';

        $docType = ucwords(str_replace('_', ' ', $this->document->type));

        if ($this->action == 'submitted') {
            $title = 'Permintaan Approval';
            $msg = "$this->actorName mengajukan $docType.";
            $icon = 'bx-file';
            $color = 'warning';
        } elseif ($this->action == 'approved') {
            $title = 'Dokumen Disetujui';
            $msg = "$docType disetujui oleh $this->actorName.";
            $icon = 'bx-check-circle';
            $color = 'success';
        } elseif ($this->action == 'rejected') {
            $title = 'Butuh Revisi';
            $msg = "$docType dikembalikan: " . substr($this->document->feedback_message, 0, 30) . '...';
            $icon = 'bx-x-circle';
            $color = 'danger';
        }

        return [
            'title' => $title,
            'message' => $msg,
            'icon' => $icon,
            'color' => $color,
            'url' => route('documents.index'),
        ];
    }
}
