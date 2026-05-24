<!DOCTYPE html>
<html>
<head>
    <title>Hasło tymczasowe</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 550px; margin: 20px auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #333; text-align: center; margin-bottom: 20px;">Odzyskiwanie konta 🔑</h2>
        <p style="font-size: 16px; color: #555; line-height: 1.5;">
            Cześć!<br>
            Otrzymaliśmy zgłoszenie o zapomnianym haśle do Twojego konta w aplikacji <strong>Game Review App</strong>. Wygenerowaliśmy dla Ciebie bezpieczne hasło tymczasowe:
        </p>
        
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 2px; border: 2px dashed #007bff; margin: 25px 0; color: #222; border-radius: 4px;">
            {{ $temporaryPassword }}
        </div>
        
        <p style="font-size: 14px; color: #666; line-height: 1.5;">
            Zaloguj się na stronie głównej, używając powyższego hasła. Ze względów bezpieczeństwa zalecamy natychmiastową zmianę hasła na własne w zakładce <strong>Profil</strong> zaraz po zalogowaniu.
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #999; text-align: center;">
            Jeśli to nie Ty prosiłeś o reset hasła, zaloguj się i zmień dane dostępowe.
        </p>
    </div>
</body>
</html>