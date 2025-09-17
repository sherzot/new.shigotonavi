<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailMessage;

    public function __construct(string $subject, string $message)
    {
        $this->subject = $subject; // `Mailable` クラスには独自の subject プロパティがあります
        $this->emailMessage = $message; // <== 私たちはそれを$emailMessageと名付けました
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.agent_notification')
            ->with(['emailMessage' => $this->emailMessage]);
            // <== Blade ページに直接転送します。
    }
}
