<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\MaterialOrderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class MaterialOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $material_order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $material_order)
    {
        $this->user = $user;
        $this->material_order = $material_order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new MaterialOrderMail($this->user, $this->material_order));
    }
}
