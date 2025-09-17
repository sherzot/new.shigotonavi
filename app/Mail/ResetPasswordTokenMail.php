<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * URLをリセット
     */
    public $resetUrl;

    /**
     * 新しい ResetPasswordTokenMail オブジェクトを作成します。
     *
     * @param string $resetUrl
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    /**
     * メール作成プロセス。
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('パスワードリセット')
                    ->view('emails.reset_password')
                    ->with([
                        'resetUrl' => $this->resetUrl,
                    ]);
    }
}
