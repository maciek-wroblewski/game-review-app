<!DOCTYPE html>
<html>
<head>
    <title>{{ __('emails.new_follower_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #333;">{{ __('emails.greeting') }}</h2>
        <p style="font-size: 16px; color: #555;">
            {{ __('emails.new_follower_message', ['username' => $follower->username]) }} <br>
            {{ __('emails.new_follower_detail', ['username' => $follower->username]) }}
        </p>
        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/users/' . $follower->username) }}" 
               style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                {{ __('emails.view_profile') }}
            </a>
        </p>
    </div>
</body>
</html>