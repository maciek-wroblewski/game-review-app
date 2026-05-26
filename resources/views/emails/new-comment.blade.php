<!DOCTYPE html>
<html>
<head>
    <title>Nowy komentarz</title>
</head>
<body>
    <h2>Cześć!</h2>
    <p>Użytkownik <strong>{{ $commenter->username }}</strong> właśnie skomentował twój post.</p>
    
    <div style="padding: 10px; border-left: 4px solid #ccc; margin: 20px 0; background-color: #f9f9f9;">
        <p style="color: #555; font-size: 12px; margin-bottom: 5px;">Treść komentarza:</p>
        <p>{{ Str::limit($comment->body, 150) }}</p>
    </div>

    <p>
        <a href="{{ url('/posts/' . $parentPost->id) }}" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            Zobacz komentarz
        </a>
    </p>
</body>
</html>