<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $commenter;
    public $comment;
    public $parentPost;

    public function __construct(User $commenter, Post $comment, Post $parentPost)
    {
        $this->commenter = $commenter;
        $this->comment = $comment;
        $this->parentPost = $parentPost;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(':username commented on your post', ['username' => $this->commenter->username]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-comment',
        );
    }
}