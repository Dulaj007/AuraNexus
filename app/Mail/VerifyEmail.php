<?php

namespace App\Mail;

use App\Models\PendingUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public PendingUser $pending;

    /**
     * Create a new message instance.
     */
    public function __construct(PendingUser $pending)
    {
        $this->pending = $pending;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verify Your AuraNexus Account')
                    ->view('emails.verify')
                    ->with([
                        'username' => $this->pending->username,
                        'verificationUrl' => route('verify.email', $this->pending->verification_token),
                    ]);
    }
}
