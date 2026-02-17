<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Email Address</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #d97706;">📧 Verify Email Address</h2>
        <p>Hi <strong>{{ $user->name }}</strong>,</p>
        <p>Please click the button below to verify your email address and activate your KejutSahur account.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}"
                style="background-color: #d97706; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Verify
                Email Address</a>
        </div>

        <p>If you did not create an account, no further action is required.</p>

        <p style="margin-top: 30px; font-size: 12px; color: #888;">
            KejutSahur System<br>
            Biar member kejut, confirm bangun.
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 10px; color: #aaa;">
            If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your
            web browser: <br>
            <a href="{{ $url }}" style="color: #d97706;">{{ $url }}</a>
        </p>
    </div>
</body>

</html>