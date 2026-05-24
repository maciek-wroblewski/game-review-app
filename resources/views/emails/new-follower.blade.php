<!DOCTYPE html>
<html>
<head>
    <title>Nowy obserwujący</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #333;">Cześć!</h2>
        <p style="font-size: 16px; color: #555;">
            Świetne wieści! Użytkownik <strong>{{ $follower->username }}</strong> właśnie zaczął Cię obserwować w aplikacji Game Review App.
        </p>
        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/users/' . $follower->username) }}" 
               style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Zobacz jego profil
            </a>
        </p>
    </div>
</body>
</html>