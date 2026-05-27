<!DOCTYPE html>
<html>
<head>
    <title>__('Your post is getting popular!')</title>
</head>
<body>
    <h2>__('Your post is getting popular!')</h2>
    <p>__('Your post just got its first 10 likes!')</p>
    
    <div style="padding: 10px; border-left: 4px solid #ccc; margin: 20px 0;">
        <p>{{ Str::limit($post->body, 100) }}</p>
    </div>

    <p>
        <a href="{{ url('/posts/' . $post->id) }}" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            __('View Post')
        </a>
    </p>
</body>
</html>