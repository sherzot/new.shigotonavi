<?php

namespace App\Mail;

use App\Models\CompanyPerson;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $companyPerson;

    public function __construct(CompanyPerson $companyPerson)
    {

        $this->companyPerson = $companyPerson;
    }
    public function build()
    {

        return $this->view('emails.verify_success')
            ->subject('会員登録が完了しました')
            ->with([
                'companyPerson' => $this->companyPerson,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Company Verify Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
