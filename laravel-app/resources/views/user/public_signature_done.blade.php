<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signature saved - {{ $general_setting->site_title ?? 'Ogera' }}</title>
    <style>
        body { margin:0; font-family: Nunito, system-ui, -apple-system, sans-serif; background: linear-gradient(180deg,#041f4a 0%,#033d2e 100%); color:#fff; min-height:100vh; }
        .wrap { max-width:640px; margin:0 auto; padding:48px 16px; text-align:center; }
        .card { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); border-radius:16px; padding:28px 22px; }
        h1 { margin:0 0 10px; }
        p { color:#d7e3ff; line-height:1.6; }
        .badge { display:inline-block; padding:6px 12px; border-radius:999px; font-weight:800; margin-bottom:12px; background:#10b981; color:#fff; }
        .sig { background:#fff; border-radius:12px; padding:12px; margin-top:18px; }
        .sig img { max-height:110px; max-width:100%; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="badge">Saved</div>
        <h1>Thank you, {{ $user->name }}</h1>
        <p>Your signature is now on file and will appear on documents issued in your name.</p>
        @if($user->signatureUrl())
            <div class="sig"><img src="{{ $user->signatureUrl() }}" alt="Your signature"></div>
        @endif
    </div>
</div>
</body>
</html>
