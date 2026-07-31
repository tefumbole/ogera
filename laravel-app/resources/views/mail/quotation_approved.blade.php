@php $company = \App\Support\SiteBrand::siteTitle(); @endphp
<div style="font-family: Arial, Helvetica, sans-serif; color:#1f2a44; max-width:600px; margin:0 auto;">
    <h2 style="margin:22px 0 12px; color:#033d2e;">Your approved quotation</h2>

    <p style="line-height:1.6;">Hello {{ $customer_name }},</p>
    <p style="line-height:1.6;">
        Thank you for approving quotation <strong>{{ $reference_no }}</strong> with {{ $company }}.
        Your signed copy is attached to this email.
    </p>

    <table style="border-collapse:collapse; margin:18px 0;">
        <tr>
            <td style="padding:6px 14px 6px 0; color:#6b7386;">Reference</td>
            <td style="padding:6px 0; font-weight:bold;">{{ $reference_no }}</td>
        </tr>
        <tr>
            <td style="padding:6px 14px 6px 0; color:#6b7386;">Grand total</td>
            <td style="padding:6px 0; font-weight:bold;">{{ $grand_total }}</td>
        </tr>
    </table>

    <p style="margin:24px 0;">
        <a href="{{ $scan_url }}" style="background:#033d2e; color:#fff; text-decoration:none; padding:13px 26px; border-radius:8px; font-weight:bold; display:inline-block;">
            View online copy
        </a>
    </p>

    <p style="line-height:1.6; font-size:13px; color:#6b7386;">
        The QR code printed on the attached document opens the same verified copy.
    </p>
</div>
