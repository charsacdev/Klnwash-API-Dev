<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReferalsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $refemail,$refname;

    public function __construct($refemail,$refname)
    {
        $this->refemail=$refemail;
        $this->refname=$refname;
    }

   
    public function build()
    {
        return $this->subject('Referal Notification with KLN Wash')
                     ->markdown('mail.referals-mail')
                     ->with($this->refemail,$this->refname);
    }
}
