<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $person;

    public function __construct($person) // ✨ モデルの代わりにstdClassまたは配列
    {

        $this->person = $person;
    }

    public function build()
    {

        return $this->view('emails.verify_success')
            ->subject('会員登録が完了しました')
            ->with([
                'person' => $this->person,
            ]);
    }
}
