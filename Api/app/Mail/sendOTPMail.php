<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendOTPMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $user_otp;
    public $expire;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name ,$user_otp,$expire_time)
    {
        $this->name = $name;
        $this->user_otp = $user_otp;
        $this->expire = $expire_time;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){
        return $this->subject('KLMS - Verify Your Email')
                    ->markdown('emails.sendOTP')->with([
            'name' => $this->name,
            'user_otp' => $this->user_otp,
            'user_expire' => $this->expire
        ]);
    }
}
