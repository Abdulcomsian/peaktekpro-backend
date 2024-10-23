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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$body,$attachments)
    {
        $this->subject = $subject;
        $this->body = $body;
        // $this->attachments = $attachments;
        $this->attachments = is_array($attachments) ? $attachments : [];
        // dd($this->subject,$this->body,$this->attachments);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {

        return new Envelope(
            subject: $this->subject
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.coc-insurance-email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $email = $this->subject($this->subject)
                      ->view('emails.coc-insurance-email')
                      ->with('body', $this->body);
    
        // Check if $this->attachments is a valid array and has items
        if (count($this->attachments) > 0) {
            foreach ($this->attachments as $file) {
                // Check if $file is a valid uploaded file instance
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $email->attach($file->getRealPath(), [
                        'as' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                    ]);
                }
            }
        }
    
        return $email;
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CocInsuranceMail extends Mailable
{
    use Queueable, SerializesModels;
    public $subject;
    public $body;
    public $attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$body,$attachments)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = is_array($attachments) ? $attachments : [];
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {

        return new Envelope(
            subject: $this->subject
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.coc-insurance-email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */

    public function attachments()
    {
        $email = $this->subject($this->subject)
                    ->view('emails.coc-insurance-email')
                    ->with('body', $this->body);

        if (is_array($this->attachments) && count($this->attachments) > 0) {
            foreach ($this->attachments as $filePath) {
                // Get the full path to the file
                $fullPath = storage_path("app/$filePath");
                if (file_exists($fullPath)) {
                    $email->attach($fullPath, [
                        'as' => basename($fullPath),
                        'mime' => mime_content_type($fullPath),
                    ]);
                } else {
                    \Log::warning('File does not exist for attachment', ['file' => $fullPath]);
                }
            }
        }

        return $email;
    }

}