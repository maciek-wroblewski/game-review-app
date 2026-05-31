<!DOCTYPE html>
<html>
<head>
    <title>{{ __('emails.new_comment_subject') }}</title>
</head>
<body>
    <h2>{{ __('emails.new_comment_title') }}</h2>
    <p>{{ __('emails.new_comment_message', ['username' => $commenter->username]) }}</p>
    
    <div style="padding: 10px; border-left: 4px solid #ccc; margin: 20px 0; background-color: #f9f9f9;">
        <p style="color: #555; font-size: 12px; margin-bottom: 5px;">{{ __('emails.comment_body') }}</p>
        <p>{{ Str::limit($comment->body, 150) }}</p>
    </div>

    <p>
        <a href="{{ url('/posts/' . $parentPost->id) }}" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            {{ __('emails.view_comment') }}
        </a>
    </p>
</body>
</html>