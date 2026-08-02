<div style="font-family: Arial, Helvetica, sans-serif; color:#1f2a44; max-width:600px; margin:0 auto;">
    <h2 style="margin:22px 0 12px; color:#033d2e;">Please add your signature</h2>

    <p style="line-height:1.6;">Hello {{ $user_name }},</p>
    <p style="line-height:1.6;">
        An account has been set up for you at {{ $company }}. Open the secure link below and draw your
        signature once — it is then placed automatically on the quotations, invoices and rental documents
        issued in your name.
    </p>

    <p style="margin:26px 0;">
        <a href="{{ $sign_url }}" style="background:#033d2e; color:#fff; text-decoration:none; padding:13px 26px; border-radius:8px; font-weight:bold; display:inline-block;">
            Add my signature
        </a>
    </p>

    <p style="line-height:1.6; font-size:13px; color:#6b7386;">
        This link works once. If the button does not work, paste this address into your browser:<br>
        <span style="word-break:break-all;">{{ $sign_url }}</span>
    </p>
</div>
