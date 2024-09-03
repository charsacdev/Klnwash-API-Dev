<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminResetLink extends Mailable
{
    use Queueable, SerializesModels;

    public $email,$code,$userId,$username;

    public function __construct($email,$code,$userId,$username)
    {
        $this->email=$email;
        $this->username=$username;
        $this->code=$code;
        $this->userId=$userId;
    }

   
    public function build()
    {
        return $this->subject('Admin Reset Password Notification')
                     ->markdown('mail.admin-reset-link')
                     ->with($this->email,$this->username,$this->code,$this->userId);
    }

}
