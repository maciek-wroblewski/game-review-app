<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $temporaryPassword;

    public function __construct($temporaryPassword)
    {
        // Przechwytujemy wygenerowane tekstowe hasło
        $this->temporaryPassword = $temporaryPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Twoje hasło tymczasowe 🔑',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-password', // Wskazujemy na widok szablonu
        );
    }
}