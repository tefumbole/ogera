<?php $general_setting = DB::table('general_settings')->find(1); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$general_setting->site_title}}</title>
    <link rel="icon" type="image/png" href="{{ asset('public/logo/'.($general_setting->site_logo ?: 'beyond-logo.png')) }}" />
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/bootstrap.min.css') }}" type="text/css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --og-forest: #033D2E;
            --og-green: #07513D;
            --og-black: #071711;
            --og-gold: #D8AD4A;
            --og-gold-light: #F1D58B;
            --og-warm: #F8F6EF;
        }
        body {
            margin: 0;
            font-family: "Jost", "Nunito", -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(140deg, var(--og-forest) 0%, var(--og-black) 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .otp-card {
            width: 100%;
            max-width: 500px;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            padding: 44px 40px 34px;
            text-align: center;
            border-top: 5px solid var(--og-gold);
        }
        .auth-logo {
            width: 96px;
            height: 96px;
            object-fit: contain;
            margin: 0 auto 18px;
            display: block;
            background: transparent;
        }
        .auth-title {
            margin: 0 0 6px;
            color: var(--og-forest);
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: clamp(40px, 7vw, 56px);
            font-weight: 600;
            line-height: 1.05;
            word-break: break-word;
        }
        .auth-subtitle {
            margin: 0 0 28px;
            color: var(--og-green);
            font-size: clamp(15px, 2.4vw, 18px);
            font-weight: 400;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }
        .otp-input {
            width: 100%;
            height: 68px;
            border: 2px solid var(--og-gold);
            border-radius: 14px;
            text-align: center;
            font-size: 34px;
            font-weight: 600;
            letter-spacing: 14px;
            padding-left: 14px;
            color: var(--og-forest);
            margin-bottom: 16px;
        }
        .otp-input:focus {
            outline: none;
            border-color: var(--og-green);
            box-shadow: 0 0 0 3px rgba(7, 81, 61, 0.18);
        }
        .btn-verify {
            width: 100%;
            height: 58px;
            border: 0;
            border-radius: 12px;
            background: var(--og-forest);
            color: var(--og-warm);
            font-size: 21px;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: background 0.2s ease;
        }
        .btn-verify:hover {
            background: var(--og-green);
        }
        .otp-meta {
            margin: 16px 0 4px;
            font-size: 16px;
            color: #5d677a;
        }
        .otp-meta strong {
            color: var(--og-forest);
        }
        .otp-resend {
            margin-top: 16px;
        }
        .otp-resend button {
            border: 0;
            background: transparent;
            color: #8a92a3;
            font-size: 16px;
            font-weight: 600;
            padding: 0;
        }
        .otp-resend button.is-ready {
            color: var(--og-green);
            cursor: pointer;
        }
        .otp-resend button:disabled {
            cursor: not-allowed;
        }
        .otp-back {
            margin-top: 20px;
        }
        .otp-back a {
            color: #5d677a;
            font-size: 16px;
            text-decoration: none;
        }
        .otp-back a:hover {
            color: var(--og-forest);
        }
        .app-version {
            margin-top: 22px;
            color: rgba(248, 246, 239, 0.7);
            font-size: 13px;
            letter-spacing: 0.06em;
        }
        .alert {
            text-align: left;
            font-size: 15px;
        }
    </style>
</head>
<body>
@php
    $appName = $general_setting->site_title ?? config('app.name', 'Application');
    $resendSeconds = isset($resend_seconds) ? (int) $resend_seconds : 0;
@endphp
<div class="otp-card">
    @if(!empty($general_setting->site_logo))
        <img src="{{ asset('public/logo/'.$general_setting->site_logo) }}" alt="{{$appName}}" class="auth-logo">
    @endif
    <h1 class="auth-title">{{$appName}}</h1>
    <p class="auth-subtitle">Secure Sign-in</p>

    @if($errors->has('name'))
        <div class="alert alert-danger">{{ $errors->first('name') }}</div>
    @endif
    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif
    @if(session()->has('not_permitted'))
        <div class="alert alert-danger">{{ session()->get('not_permitted') }}</div>
    @endif
    @if(!empty($whatsapp_error))
        <div class="alert alert-warning">{{ $whatsapp_error }}</div>
    @endif
    @if(!empty($local_otp_code))
        <div class="alert" style="background:#eef6f1;color:var(--og-forest);border:1px solid var(--og-gold);">
            Your verification code: <strong style="letter-spacing:6px;font-size:1.4rem;color:var(--og-forest);">{{ $local_otp_code }}</strong>
        </div>
    @endif

    @if(Auth::user()->is_active)
        <form action="{{ route('check.otp.store') }}" method="post">
            @csrf
            <input id="otp-code" class="otp-input" type="text" name="otp" maxlength="6" placeholder="000000" required inputmode="numeric" autocomplete="one-time-code">
            @if ($errors->has('otp'))
                <div class="alert alert-danger">{{ $errors->first('otp') }}</div>
            @endif
            <div class="otp-meta">Code expires in <strong id="otp-timer">5:00</strong></div>
            <button type="submit" class="btn-verify">Verify OTP</button>
        </form>

        <div class="otp-resend">
            <form id="otp-resend-form" action="{{ route('check.otp.resend') }}" method="post">
                @csrf
                <button type="submit" id="otp-resend-btn" disabled>
                    Resend OTP in <span id="resend-countdown">{{ max(0, $resendSeconds) }}</span>s
                </button>
            </form>
        </div>

        <div class="otp-back">
            <a href="{{ route('login') }}">&larr; Back to Login</a>
        </div>
    @else
        <div class="alert alert-warning">You are logged in but the account is not active. Please contact admin.</div>
    @endif
</div>

<div class="app-version">{{ \App\Support\AppVersion::display() }}</div>

<script>
    (function () {
        var otpInput = document.getElementById('otp-code');
        if (otpInput) {
            otpInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        }

        var timerEl = document.getElementById('otp-timer');
        var resendBtn = document.getElementById('otp-resend-btn');
        var resendCountdownEl = document.getElementById('resend-countdown');
        var expirySeconds = 300;
        var resendSeconds = {{ max(0, $resendSeconds) }};

        function formatTime(total) {
            var min = Math.floor(total / 60);
            var sec = total % 60;
            return min + ':' + (sec < 10 ? '0' + sec : sec);
        }

        function tickExpiry() {
            if (expirySeconds <= 0) {
                if (timerEl) {
                    timerEl.textContent = '0:00';
                }
                return;
            }
            if (timerEl) {
                timerEl.textContent = formatTime(expirySeconds);
            }
            expirySeconds -= 1;
            setTimeout(tickExpiry, 1000);
        }

        function tickResend() {
            if (resendSeconds <= 0) {
                if (resendBtn) {
                    resendBtn.disabled = false;
                    resendBtn.classList.add('is-ready');
                    resendBtn.innerHTML = 'Resend OTP';
                }
                return;
            }
            if (resendCountdownEl) {
                resendCountdownEl.textContent = resendSeconds;
            }
            resendSeconds -= 1;
            setTimeout(tickResend, 1000);
        }

        tickExpiry();
        tickResend();
    })();
</script>
</body>
</html>
