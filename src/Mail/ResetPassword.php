<?php

namespace App\Mail;

use App\ForgotPasswordCode;
use App\PasswordResetCode;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $userCode = ForgotPasswordCode::where('user_id',$this->user->id)->first();
        return $this->view('Mail.Mail')
            ->with([
            'UserName' => $this->user->name,
            'Code'     => $userCode->code,
        ])->to($this->user->email);
    }
}
