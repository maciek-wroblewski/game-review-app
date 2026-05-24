<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFollowerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $follower;

    public function __construct(User $follower)
    {
        $this->follower = $follower;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Masz nowego obserwującego! 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-follower',
        );
    }
}