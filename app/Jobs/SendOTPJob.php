<?php

namespace App\Jobs;

use App\Mail\SendOTPMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class SendOTPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $otp;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(['test']);
    //       Log::info('Sending OTP email', [
    //     'email' => $this->user->email,
    //     'otp' => $this->otp,
    //     'job' => self::class,
    //     'timestamp' => now()->toDateTimeString(),
    // ]);
        Mail::to($this->user->email)->send(new SendOTPMail($this->user, $this->otp));
    }
}
