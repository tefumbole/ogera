<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add your signature - {{ $general_setting->site_title ?? 'Ogera' }}</title>
    <style>
        body { margin:0; font-family: Nunito, system-ui, -apple-system, sans-serif; background: linear-gradient(180deg,#041f4a 0%,#033d2e 100%); color:#fff; min-height:100vh; }
        .wrap { max-width:640px; margin:0 auto; padding:48px 16px; }
        .card { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); border-radius:16px; padding:28px 22px; }
        h1 { margin:0 0 10px; font-size:26px; }
        p { color:#d7e3ff; line-height:1.6; }
        .who { background:rgba(255,255,255,.1); border-radius:12px; padding:14px 16px; margin:20px 0; }
        .who strong { display:block; font-size:18px; }
        .who span { color:#b9cdf5; font-size:14px; }
        .current { background:#fff; border-radius:12px; padding:10px; margin:0 0 18px; text-align:center; }
        .current img { max-height:90px; max-width:100%; }
        .actions { margin-top:22px; }
        .cta { display:inline-block; background:#c6ab47; color:#08210f; border:0; border-radius:10px; padding:13px 22px; font-size:16px; font-weight:800; cursor:pointer; }
        .cta[disabled] { opacity:.5; cursor:not-allowed; }
        .ghost { background:transparent; border:1px solid rgba(255,255,255,.4); color:#fff; border-radius:10px; padding:12px 18px; font-size:15px; margin-left:8px; cursor:pointer; }
        .preview { background:#fff; border-radius:12px; padding:12px; margin-top:18px; text-align:center; }
        .preview img { max-height:120px; max-width:100%; }
        .alert { background:#ef4444; border-radius:10px; padding:12px 14px; margin-bottom:18px; }
        .note { font-size:13px; color:#a9bfe6; margin-top:22px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @if(session()->has('not_permitted'))
            <div class="alert">{{ session()->get('not_permitted') }}</div>
        @endif

        <h1>Add your signature</h1>
        <p>
            {{ $general_setting->site_title ?? 'Ogera' }} needs your handwritten signature. Draw it once here and it
            will be placed on the quotations, invoices and rental documents issued in your name.
        </p>

        <div class="who">
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->email }}</span>
        </div>

        @if($user->hasSignature())
            <p><strong>Signature currently on file</strong> — signing again replaces it.</p>
            <div class="current"><img src="{{ $user->signatureUrl() }}" alt="Current signature"></div>
        @endif

        <form method="POST" action="{{ route('user.signature.submit', $token) }}" id="sig-form">
            @csrf
            <input type="hidden" name="signature_data" id="signature_data">

            <div class="preview" id="sig-preview" style="display:none;">
                <img data-signature-preview="signature_data" alt="Your signature">
            </div>

            <div class="actions">
                <button type="button" class="cta" data-signature-pad="signature_data">Draw my signature</button>
                <button type="submit" class="ghost" id="sig-submit" disabled>Save signature</button>
            </div>
        </form>

        <p class="note">This link works once. If you need to change your signature afterwards, ask the team to send a new one.</p>
    </div>
</div>

@include('components.signature_pad')

<script>
    document.getElementById('signature_data').addEventListener('change', function () {
        var filled = this.value !== '';
        document.getElementById('sig-submit').disabled = !filled;
        document.getElementById('sig-preview').style.display = filled ? '' : 'none';
    });
</script>
</body>
</html>
