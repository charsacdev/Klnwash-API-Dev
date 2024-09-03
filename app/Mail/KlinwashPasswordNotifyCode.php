<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KlinwashPasswordNotifyCode extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$username;

    public function __construct($email,$username)
    {
        $this->email=$email;
        $this->username=$username;
    }

   
    public function build()
    {
        return $this->subject('Password Update Confirmation')
                     ->markdown('mail.klinwash-password-notify-code')
                     ->with($this->email,$this->username);
    }

}
