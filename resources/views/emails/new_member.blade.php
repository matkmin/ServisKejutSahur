<!DOCTYPE html>
<html>

<head>
    <title>New Member Registration</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #d97706;">🌙 New Member Alert!</h2>
        <p>Hi <strong>{{ $agent->name }}</strong>,</p>
        <p>A new member has just registered under your referral code!</p>

        <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
            <p><strong>Package:</strong> {{ ucfirst(str_replace('_', ' ', $user->package)) }}</p>
            @if(count($user->add_on) > 0)
                    <p><strong>Add-ons:</strong>
                        {{ implode(', ', array_map(function ($a) {
                return ucfirst(str_replace('_', ' ', $a)); }, $user->add_on)) }}
                    </p>
            @endif
            <p><strong>Sahur Time:</strong> {{ $user->sahur_time }}</p>
        </div>

        <p>Please check your dashboard to verify payment and manage this member.</p>

        <p style="margin-top: 30px; font-size: 12px; color: #888;">
            KejutSahur System<br>
            Biar member kejut, confirm bangun.
        </p>
    </div>
</body>

</html>