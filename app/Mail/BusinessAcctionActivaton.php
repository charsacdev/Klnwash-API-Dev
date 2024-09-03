<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusinessAcctionActivaton extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$businessName,$username;

    public function __construct($email,$businessName,$username)
    {
        $this->email=$email;
        $this->businessName=$businessName;
        $this->username=$username;
    }

   
    public function build()
    {
        return $this->subject('Request for Business Profile')
                     ->markdown('mail.business-acction-activaton')
                     ->with($this->email,$this->businessName,$this->username);
    }

}
