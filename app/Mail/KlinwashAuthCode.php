<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KlinwashAuthCode extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$code,$username;

    public function __construct($email,$code,$username)
    {
        $this->email=$email;
        $this->code=$code;
        $this->username=$username;
    }

   
    public function build()
    {
        return $this->subject('KLN Wash Authentication Code')
                     ->markdown('mail.klinwash-auth-code')
                     ->with($this->email,$this->code,$this->username);
    }
}
