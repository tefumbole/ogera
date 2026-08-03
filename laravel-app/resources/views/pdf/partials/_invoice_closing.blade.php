{{--
    The foot of an invoice-style document.

    Signatures sit side by side on one line — the client on the left, whoever
    issued the document on the right — and the QR code and barcode are centred
    underneath. Used by the quotation, sale, booking and rental templates so
    they all close the same way.

    Expected variables (all optional):
      $creator          the User who issued the document; their `sign` is stamped
      $clientSignature  data URI of the client's signature
      $clientName       name printed under the client signature
      $clientSignedAt   Carbon instance, printed as "Signed d-m-Y H:i"
      $clientLabel      heading over the client signature
      $adminLabel       heading over the issuer's signature
      $qrData           string encoded into the QR code; omit to hide it
      $barcodeData      string encoded into the barcode; omit to hide it
      $qrSize           QR side length in px (default 78)
--}}
@php
    use App\Support\UserSignature;

    $creator = $creator ?? null;
    $clientSignature = $clientSignature ?? null;
    $clientName = $clientName ?? null;
    $clientSignedAt = $clientSignedAt ?? null;
    $clientLabel = $clientLabel ?? 'Approved & signed by client';
    $qrData = $qrData ?? null;
    $barcodeData = $barcodeData ?? null;
    $qrSize = $qrSize ?? 78;

    $adminSignature = UserSignature::documentSignature($creator);
    $hasClient = ! empty($clientSignature);
    $hasIssuer = ! empty($adminSignature) || ! empty($creator);

    // Only claim to sign for the company when there is a signature to show;
    // otherwise this is just the credit line it replaced.
    $adminLabel = $adminLabel ?? ($adminSignature
        ? 'For and on behalf of ' . \App\Support\SiteBrand::siteTitle()
        : trans('file.Created By'));
@endphp

@if($hasClient || $hasIssuer)
<table class="inv-closing">
    <tr>
        <td class="inv-closing-sign">
            @if($hasClient)
                <span class="inv-label">{{ $clientLabel }}</span>
                <img class="inv-signature-img" src="{{ $clientSignature }}" alt="Client signature">
                <div class="inv-signature-name">{{ $clientName ?: 'Client' }}</div>
                @if($clientSignedAt)
                    <div class="inv-signature-meta">Signed {{ $clientSignedAt->format('d-m-Y H:i') }}</div>
                @endif
            @endif
        </td>
        <td class="inv-closing-sign inv-closing-sign-right">
            @if($hasIssuer)
                <span class="inv-label">{{ $adminLabel }}</span>
                @if($adminSignature)
                    <img class="inv-signature-img" src="{{ $adminSignature }}" alt="Authorised signature">
                @endif
                <div class="inv-signature-name">{{ $creator->name ?? '' }}</div>
                @if(! empty($creator->email))
                    <div class="inv-signature-meta">{{ $creator->email }}</div>
                @endif
            @endif
        </td>
    </tr>
</table>
@endif

@if($qrData || $barcodeData)
<div class="inv-codes-center">
    @if($qrData)
        <div class="inv-qr">
            <?php echo '<img src="data:image/png;base64,'.DNS2D::getBarcodePNG($qrData, 'QRCODE').'" height="'.(int) $qrSize.'" width="'.(int) $qrSize.'" alt="qrcode">'; ?>
        </div>
    @endif
    @if($barcodeData)
        <div class="inv-barcode">
            <?php echo '<img src="data:image/png;base64,'.DNS1D::getBarcodePNG($barcodeData, 'C128').'" height="24" width="160" alt="barcode">'; ?>
        </div>
    @endif
</div>
@endif

@if(\App\Support\Reviews::outboundCtaEnabled())
    <p style="text-align:center; margin: 8px 0 0; font-size: 10px; color:#4b5563;">
        Enjoyed our service? Share a quick review at
        <span style="color:#033d2e; font-weight:600;">{{ \App\Support\Reviews::publicUrl() }}</span>
    </p>
@endif
