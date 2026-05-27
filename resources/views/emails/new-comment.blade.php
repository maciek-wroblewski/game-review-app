<!DOCTYPE html>
<html>
<head>
    <title>__('New comment')</title>
</head>
<body>
    <h2>__('New comment')</h2>
    <p>__('A user named :username has commented on your post.', ['username' => $commenter->username])</p>
    
    <div style="padding: 10px; border-left: 4px solid #ccc; margin: 20px 0; background-color: #f9f9f9;">
        <p style="color: #555; font-size: 12px; margin-bottom: 5px;">__('Comment body:')}</p>
        <p>{{ Str::limit($comment->body, 150) }}</p>
    </div>

    <p>
        <a href="{{ url('/posts/' . $parentPost->id) }}" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            __('View Comment')
        </a>
    </p>
</body>
</html>