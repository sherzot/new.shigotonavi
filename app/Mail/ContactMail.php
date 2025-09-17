<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * 新しいメッセージ インスタンスを作成します。
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * メッセージを構築します。
     */
    public function build()
    {
        return $this->subject("{$this->data['form_type']}")
                    ->view('emails.sendcontact')
                    ->with('data', $this->data);
    }
}
