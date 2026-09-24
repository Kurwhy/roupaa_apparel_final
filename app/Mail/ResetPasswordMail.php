<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $resetToken;
    public string $resetUrl;
    public string $email;

    public function __construct(string $userName, string $token, string $email)
    {
        $this->userName   = $userName;
        $this->resetToken = $token;
        $this->resetUrl   = url('/reset-password/' . $token . '?email=' . urlencode($email));
        $this->email      = $email;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ROUPAA Apparel | Reset Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
        );
    }
}
