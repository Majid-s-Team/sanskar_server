<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Password Reset OTP - Sanskar Academy')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'name' => $this->user->father_name ?? 'User'
            ]);
    }
}
