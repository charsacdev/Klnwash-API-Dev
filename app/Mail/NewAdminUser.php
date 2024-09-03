<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAdminUser extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$username,$resetId;

    public function __construct($email,$username,$resetId)
    {
        $this->email=$email;
        $this->username=$username;
        $this->resetId=$resetId;
    }

   
    public function build()
    {
        return $this->subject('Admin Registration Notification')
                     ->markdown('mail.new-admin-user')
                     ->with($this->email,$this->username,$this->resetId);
    }

}
