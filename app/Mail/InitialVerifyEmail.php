<?php

namespace App\Mail;

use App\Models\MasterPerson;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class InitialVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $person;

    public function __construct(MasterPerson $person)
    {
        $this->person = $person;
    }

    public function build()
    {
        $verificationUrl = route('verify.email', ['token' => $this->person->verification_token]);
        Log::info('Sending verification email', [
            'verificationUrl' => $verificationUrl,
        ]);

        return $this->view('emails.verify')
            ->subject('会員登録手続きのお知らせ')
            ->with([
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
