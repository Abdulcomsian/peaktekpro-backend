<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notifiable;

class CustomNotifiable extends Notification
{
    use Notifiable;
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function routeNotificationForMail()
    {
        return $this->email; 
    }
}
