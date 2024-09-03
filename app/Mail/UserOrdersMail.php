<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserOrdersMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$username,$codeGen;

    public function __construct($email,$username,$codeGen)
    {
        $this->email=$email;
        $this->username=$username;
        $this->codeGen=$codeGen;
    }

   
    public function build()
    {
        return $this->subject('Orders Notification with KLN Wash')
                     ->markdown('mail.user-orders')
                     ->with($this->email,$this->username,$this->codeGen);
    }


}
