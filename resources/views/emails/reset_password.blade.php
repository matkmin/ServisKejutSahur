<!DOCTYPE html>
<html>

<head>
    <title>Reset Password Notification</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #d97706;">🔒 Reset Password</h2>
        <p>Hi <strong>{{ $user->name }}</strong>,</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}"
                style="background-color: #d97706; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Reset
                Password</a>
        </div>

        <p>This password reset link will expire in 60 minutes.</p>
        <p>If you did not request a password reset, no further action is required.</p>

        <p style="margin-top: 30px; font-size: 12px; color: #888;">
            KejutSahur System<br>
            Biar member kejut, confirm bangun.
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 10px; color: #aaa;">
            If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web
            browser: <br>
            <a href="{{ $url }}" style="color: #d97706;">{{ $url }}</a>
        </p>
    </div>
</body>

</html>