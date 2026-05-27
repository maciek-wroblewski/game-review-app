<!DOCTYPE html>
<html>
<head>
    <title>__('Temporary password')</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 550px; margin: 20px auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #333; text-align: center; margin-bottom: 20px;">__('Account recovery 🔑')</h2>
        <p style="font-size: 16px; color: #555; line-height: 1.5;">
            __('Hello!')<br>
            __('We have received a request to reset the password for your account in the Game Review App. We have generated a temporary password for you:')
        </p>
        
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 2px; border: 2px dashed #007bff; margin: 25px 0; color: #222; border-radius: 4px;">
            {{ $temporaryPassword }}
        </div>
        
        <p style="font-size: 14px; color: #666; line-height: 1.5;">
            __('Log in to the main page using the above password. For security reasons, we recommend changing your password to your own in the <strong>Profile</strong> tab immediately after logging in.')
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #999; text-align: center;">
            __('If this was not you who requested a password reset, please log in and change your credentials.')
        </p>
    </div>
</body>
</html>