@php $company = \App\Support\SiteBrand::siteTitle(); @endphp
<div style="font-family: Arial, Helvetica, sans-serif; color:#1f2a44; max-width:600px; margin:0 auto;">
    @if(!empty($header))
        <img src="{{ url('public/logo', $header) }}" alt="{{ $company }}" style="width:100%; display:block;">
    @endif

    <h2 style="margin:22px 0 12px; color:#033d2e;">Quotation for your approval</h2>

    <p style="line-height:1.6;">Hello {{ $customer_name }},</p>
    <p style="line-height:1.6;">
        {{ $company }} has prepared quotation <strong>{{ $reference_no }}</strong> for you.
        Open the secure link below to read the full quotation and approve or reject it.
    </p>

    <p style="margin:26px 0;">
        <a href="{{ $approval_url }}" style="background:#033d2e; color:#fff; text-decoration:none; padding:13px 26px; border-radius:8px; font-weight:bold; display:inline-block;">
            Open quotation
        </a>
    </p>

    <p style="line-height:1.6; font-size:13px; color:#6b7386;">
        Your signed copy, with a QR code for online verification, is sent to you as soon as you approve.
        If the button does not work, paste this address into your browser:<br>
        <span style="word-break:break-all;">{{ $approval_url }}</span>
    </p>

    @if(!empty($footer))
        <img src="{{ url('public/logo', $footer) }}" alt="" style="width:100%; display:block; margin-top:24px;">
    @endif
</div>
