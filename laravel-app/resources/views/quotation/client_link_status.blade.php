<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $headline ?? 'Quotation' }} - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <style>
        body { margin:0; font-family: Nunito, system-ui, sans-serif; background: linear-gradient(180deg,#041f4a 0%,#033d2e 100%); color:#fff; min-height:100vh; }
        .wrap { max-width:640px; margin:0 auto; padding:48px 16px; text-align:center; }
        .card { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); border-radius:16px; padding:28px 22px; }
        h1 { margin:0 0 10px; font-size:1.45rem; }
        p { color:#d7e3ff; line-height:1.6; margin:0 0 10px; }
        .badge { display:inline-block; padding:6px 12px; border-radius:999px; font-weight:800; margin-bottom:12px; }
        .badge-approved { background:#22c55e; color:#052e16; }
        .badge-rejected { background:#ef4444; color:#fff; }
        .badge-expired { background:#f59e0b; color:#111; }
        .ref { color:#c6ab47; font-weight:700; margin-top:8px; }
    </style>
</head>
<body>
@php
    $status = $status ?? 'expired';
    $badgeClass = $status === 'approved' ? 'badge-approved' : ($status === 'rejected' ? 'badge-rejected' : 'badge-expired');
@endphp
<div class="wrap">
    <div class="card">
        <div class="badge {{ $badgeClass }}">{{ $badge ?? 'Link expired' }}</div>
        <h1>{{ $headline ?? 'This quotation link is no longer valid' }}</h1>
        <div style="margin-bottom:14px;">@include('booking.partials.agreement_brand', ['compact' => true])</div>
        <p>{{ $body ?? 'This link has expired or has already been used. If you still need to review or sign, please contact '.\App\Support\SiteBrand::siteTitle($general_setting ?? null).' for a new link.' }}</p>
        @if(!empty($reference))
            <p class="ref">Ref {{ $reference }}</p>
        @endif
    </div>
</div>
</body>
</html>
