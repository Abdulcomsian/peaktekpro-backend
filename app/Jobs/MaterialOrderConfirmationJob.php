<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaterialOrderConfirmationMail;

class MaterialOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $email;
    public $subject;
    public $body;
    public $attachments;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,$subject,$body,$attachments)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = is_array($attachments) ? $attachments : [];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new MaterialOrderConfirmationMail($this->subject, $this->body, $this->attachments));
    }
}
