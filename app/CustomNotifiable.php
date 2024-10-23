<?php

namespace App;
use Illuminate\Notifications\Notifiable;

class CustomNotifiable
{
    use Notifiable;
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
        return $this->email; // Specify the email for the notification
    }
}
