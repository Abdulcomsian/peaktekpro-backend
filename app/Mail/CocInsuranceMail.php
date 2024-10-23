<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CocInsuranceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $attachments;

    public function __construct($subject, $body, $attachments)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = is_array($attachments) ? $attachments : [];
    }

    public function build()
    {
        $email = $this->view('emails.coc-insurance-email')
                      ->subject($this->subject)
                      ->with(['body' => $this->body]);

        // Attach files
        foreach ($this->attachments as $file) {
            if (is_string($file)) { // Ensure it's a valid file path
                $email->attach($file);
            }
        }

        return $email;
    }
}

