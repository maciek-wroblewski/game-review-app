<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPostMail extends Mailable
{
    use Queueable, SerializesModels;

    public $author;
    public $post;

    public function __construct(User $author, Post $post)
    {
        $this->author = $author;
        $this->post = $post;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New post from :username', ['username' => $this->author->username]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-post',
        );
    }
}