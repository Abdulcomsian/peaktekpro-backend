<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CocInsuranceNotification extends Notification
{
    use Queueable;

    protected $subject;
    protected $body;
    protected $attachments;

    public function __construct($subject, $body, $attachments)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = $attachments;
    }

    public function via($notifiable)
    {
        return ['mail']; 
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->line($this->body);

        // Attach files
        foreach ($this->attachments as $filePath) {
            $fullPath = storage_path("app/$filePath");
            if (file_exists($fullPath)) {
                $mail->attach($fullPath);
            } else {
                \Log::warning('File does not exist for attachment', ['file' => $fullPath]);
            }
        }

        return $mail;
    }
}