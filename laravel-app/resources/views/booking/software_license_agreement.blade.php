<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Software License Subscription Agreement - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" />
    @include('booking.partials.agreement_styles')
</head>
<body>
<div class="wrap">
    <div class="hero">
        @include('booking.partials.agreement_brand')
        <h1>Software License Subscription Agreement</h1>
        <p>Booking Ref: <strong>{{ $booking->reference_no }}</strong> | Client: <strong>{{ $booking->customer->name ?? '' }}</strong></p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div id="agreement-content">
        <div class="card">
            <div class="card-head"><div class="num">1</div><h3>Subscription Summary</h3></div>
            <p>You have subscribed for the product(s) / service(s) listed below. Your subscription period runs from the start date to the end (expiry) date shown for each item. This covers software licenses and digital services such as IPTV, antivirus, and related subscriptions.</p>
            <div class="table-wrap">
                <table class="equipment">
                    <thead>
                        <tr>
                            <th>Product / Service</th>
                            <th>Code</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>From</th>
                            <th>To (Expires)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td data-label="Product / Service">{{ $item['name'] }}</td>
                                <td data-label="Code">{{ $item['code'] }}</td>
                                <td data-label="Qty">{{ $item['qty'] }}</td>
                                <td data-label="Price">{{ number_format($item['unit_price'], 2) }}</td>
                                <td data-label="Subtotal">{{ number_format($item['total'], 2) }}</td>
                                <td data-label="From">{{ $item['start'] ? date('d M Y', strtotime($item['start'])) : 'As agreed' }}</td>
                                <td data-label="To (Expires)">{{ $item['end'] ? date('d M Y', strtotime($item['end'])) : 'As agreed' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">2</div><h3>Access &amp; Credentials</h3></div>
            <p>After you sign this agreement and our team approves it, you will receive access to your <strong>client portal</strong> (login details via WhatsApp). Use the portal to view your subscription, signed contract, and related documents. Keep your login credentials confidential.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">3</div><h3>Fair Use &amp; License Scope</h3></div>
            <p>The subscription is for your personal or organizational use as registered under this booking. Sharing credentials beyond the agreed seats/qty, reselling access, or using the service for unlawful purposes is prohibited and may result in immediate suspension without refund.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">4</div><h3>Renewal &amp; Expiry</h3></div>
            <p>Service access continues through the expiry date listed above. Renewal is not automatic unless separately agreed. After expiry, access may be suspended until a new subscription is purchased or renewed.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">5</div><h3>Support &amp; Service Changes</h3></div>
            <p>Support is provided for the subscribed products during the active period. Third-party platforms (e.g. IPTV providers, antivirus vendors) may change features or availability; we will notify you of material changes where practical.</p>
        </div>
        @if(!empty($booking->booking_note))
        <div class="card">
            <div class="card-head"><div class="num">6</div><h3>Additional Notes</h3></div>
            <div class="booking-note-content">{!! \App\Support\BookingNoteFormatter::forDisplay($booking->booking_note) !!}</div>
        </div>
        @endif
        <div class="card">
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '7' : '6' }}</div><h3>Payment Information</h3></div>
            <p>Grand Total: <strong>{{ number_format($booking->grand_total, 2) }}</strong></p>
            <p>Amount Paid: <strong>{{ number_format($booking->paid_amount, 2) }}</strong></p>
            <p>Balance Due: <strong>{{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</strong></p>
            @if(isset($payments) && $payments->count())
                <ul>
                    @foreach($payments as $payment)
                        <li>{{ $payment->paying_method }} — {{ number_format($payment->amount, 2) }} @if($payment->change > 0)(Change: {{ number_format($payment->change, 2) }})@endif</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="card">
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '8' : '7' }}</div><h3>Acceptance</h3></div>
            <p>By signing below, the client confirms they have subscribed for the product(s) listed, accept the subscription period (From–To), and agree to the terms of this Software License Subscription Agreement. Identity verification via ID card upload is required.</p>
        </div>
    </div>

    <form id="sign-form" method="POST" action="{{ route('rental.agreement.sign', $contract->signature_token) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="signature_image" id="signature_image">
        <input type="hidden" name="agreement_read_confirmed" id="agreement_read_confirmed" value="0">

        <div class="signature-box">
            <h4>⚠ Signature Required</h4>
            <p>A digital signature and valid ID card are required to complete this software license subscription agreement.</p>
            <button type="button" class="btn btn-outline" id="open-signature-modal" disabled>Add Signature</button>
            <img id="signature-preview" class="preview-signature" alt="Signature preview">

            @include('booking.partials.agreement_id_card_fields')
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="agreement_accepted" id="agreement_accepted" value="1" disabled>
            <label for="agreement_accepted">I have read and agree to the Software License Subscription Agreement and confirm that all information provided is accurate.</label>
        </div>
        <p style="font-size:13px;margin:8px 0 0;color:#c9d4e8;">Tick the box above, then tap <strong>Submit Agreement</strong> at the bottom.</p>
        <noscript>
            <button type="submit" class="btn btn-accent" style="margin-top:12px;">✓ Submit Agreement</button>
        </noscript>
    </form>
</div>

<div class="footer-bar">
    <div class="footer-inner">
        <div>Do you accept the terms of this software license subscription?</div>
        <div class="footer-actions">
            <a href="{{ route('login') }}" class="btn btn-danger-outline">I Disagree</a>
            <button type="button" class="btn btn-accent" id="submit-agreement">✓ Submit Agreement</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="signature-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Sign Your Agreement</h3>
            <p style="margin:0;color:#6f7b91;">Draw your signature in the box below using your finger, mouse, or trackpad.</p>
        </div>
        <div class="modal-body">
            <canvas id="signature-pad"></canvas>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger-outline" id="close-signature-modal">✕ Cancel</button>
            <button type="button" class="btn btn-outline" id="clear-signature">Clear</button>
            <button type="button" class="btn btn-primary" id="confirm-signature">✓ Confirm Signature</button>
        </div>
    </div>
</div>

@include('booking.partials.agreement_sign_script')

</body>
</html>
