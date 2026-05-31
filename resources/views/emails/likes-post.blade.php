<!DOCTYPE html>
<html>
<head>
    <title>{{ __('emails.likes_post_subject') }}</title>
</head>
<body>
    <h2>{{ __('emails.likes_post_title') }}</h2>
    <p>{{ __('emails.likes_post_message') }}</p>
    
    <div style="padding: 10px; border-left: 4px solid #ccc; margin: 20px 0;">
        <p>{{ Str::limit($post->body, 100) }}</p>
    </div>

    <p>
        <a href="{{ url('/posts/' . $post->id) }}" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            {{ __('emails.view_post') }}
        </a>
    </p>
</body>
</html>