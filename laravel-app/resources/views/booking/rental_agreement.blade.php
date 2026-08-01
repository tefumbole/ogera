<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Equipment Rental Agreement - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" />
    @include('booking.partials.agreement_styles')
</head>
<body>
<div class="wrap">
    <div class="hero">
        @include('booking.partials.agreement_brand')
        <h1>Equipment Rental Long-Term Agreement</h1>
        <p>Booking Ref: <strong>{{ $booking->reference_no }}</strong> | Client: <strong>{{ $booking->customer->name ?? '' }}</strong></p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div id="agreement-content">
        <div class="card">
            <div class="card-head"><div class="num">1</div><h3>Rental Term & Return Time</h3></div>
            <p>This agreement covers the long-term rental of equipment listed below. All rented equipment must be returned by the agreed return date and time shown for each item. Failure to return on time will incur penalties.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">2</div><h3>Late Return Penalties</h3></div>
            <p>Late return of any equipment will incur penalties including an <strong>additional full-day rental charge per day</strong> (or part thereof) for each item kept beyond the agreed return time, plus any applicable administrative fees.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">3</div><h3>Client Responsibility for Damage</h3></div>
            <p>Broken, lost, stolen, or damaged equipment is the <strong>full responsibility of the client</strong>. The client agrees to pay repair or replacement costs at the current market value of the affected equipment.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">4</div><h3>Equipment List & Pricing</h3></div>
            <div class="table-wrap">
                <table class="equipment">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Code</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Return By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td data-label="Equipment">{{ $item['name'] }}</td>
                                <td data-label="Code">{{ $item['code'] }}</td>
                                <td data-label="Qty">{{ $item['qty'] }}</td>
                                <td data-label="Unit Price">{{ number_format($item['unit_price'], 2) }}</td>
                                <td data-label="Subtotal">{{ number_format($item['total'], 2) }}</td>
                                <td data-label="Return By">{{ $item['end'] ? date('d M Y, H:i', strtotime($item['end'])) : 'As scheduled' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p style="margin-top:12px;">Grand Total: <strong>{{ number_format($booking->grand_total, 2) }}</strong></p>
        </div>
        @if(!empty($booking->booking_note))
        <div class="card">
            <div class="card-head"><div class="num">5</div><h3>Booking Notes</h3></div>
            <div class="booking-note-content">{!! \App\Support\BookingNoteFormatter::forDisplay($booking->booking_note) !!}</div>
        </div>
        @endif
        <div class="card">
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '6' : '5' }}</div><h3>Payment Information</h3></div>
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
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '7' : '6' }}</div><h3>Acceptance</h3></div>
            <p>By signing below, the client confirms they have read this rental long agreement, accept all terms, and authorize identity verification via ID card upload.</p>
        </div>
    </div>

    <form id="sign-form" method="POST" action="{{ route('rental.agreement.sign', $contract->signature_token) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="signature_image" id="signature_image">
        <input type="hidden" name="agreement_read_confirmed" id="agreement_read_confirmed" value="0">

        <div class="signature-box">
            <h4>⚠ Signature Required</h4>
            <p>A digital signature and valid ID card are required to complete this equipment rental agreement.</p>
            <button type="button" class="btn btn-outline" id="open-signature-modal" disabled>Add Signature</button>
            <img id="signature-preview" class="preview-signature" alt="Signature preview">

            @include('booking.partials.agreement_id_card_fields')
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="agreement_accepted" id="agreement_accepted" value="1" disabled>
            <label for="agreement_accepted">I have read and agree to the Equipment Rental Long Agreement and confirm that all information provided is accurate.</label>
        </div>
        <p style="font-size:13px;margin:8px 0 0;color:#c9d4e8;">Tick the box above, then tap <strong>Submit Agreement</strong> at the bottom.</p>
        <noscript>
            <button type="submit" class="btn btn-accent" style="margin-top:12px;">✓ Submit Agreement</button>
        </noscript>
    </form>
</div>

<div class="footer-bar">
    <div class="footer-inner">
        <div>Do you accept the terms outlined in this rental agreement?</div>
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
