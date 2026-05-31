<!DOCTYPE html>
<html>
<head>
    <title>{{ __('emails.temporary_password_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 550px; margin: 20px auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #333; text-align: center; margin-bottom: 20px;">{{ __('emails.account_recovery') }}</h2>
        <p style="font-size: 16px; color: #555; line-height: 1.5;">
            {{ __('emails.greeting') }}<br>
            {{ __('emails.password_reset_request') }}
        </p>
        
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 2px; border: 2px dashed #007bff; margin: 25px 0; color: #222; border-radius: 4px;">
            {{ $temporaryPassword }}
        </div>
        
        <p style="font-size: 14px; color: #666; line-height: 1.5;">
            {{ __('emails.password_reset_instructions') }}
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #999; text-align: center;">
            {{ __('emails.password_reset_not_you') }}
        </p>
    </div>
</body>
</html>