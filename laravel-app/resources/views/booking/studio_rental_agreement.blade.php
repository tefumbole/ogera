<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Studio Rental Agreement - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" />
    @include('booking.partials.agreement_styles')
</head>
<body>
<div class="wrap">
    <div class="hero">
        @include('booking.partials.agreement_brand')
        <h1>Studio Rental Agreement</h1>
        <p>Booking Ref: <strong>{{ $booking->reference_no }}</strong> | Client: <strong>{{ $booking->customer->name ?? '' }}</strong></p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div id="agreement-content">
        <div class="card">
            <div class="card-head"><div class="num">1</div><h3>Studio Session Summary</h3></div>
            <p>Studio rentals may be booked on an <strong>Hourly</strong>, <strong>Daily</strong>, or <strong>Monthly</strong> basis. The session(s) below show your agreed booking method, duration, and schedule.</p>
            <div class="table-wrap">
                <table class="equipment">
                    <thead>
                        <tr>
                            <th>Studio / Service</th>
                            <th>Code</th>
                            <th>Method</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>From</th>
                            <th>To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td data-label="Studio / Service">{{ $item['name'] }}</td>
                                <td data-label="Code">{{ $item['code'] }}</td>
                                <td data-label="Method">{{ $item['booking_method'] ?? 'Hourly' }}@if(!empty($item['number_duration'])) × {{ $item['number_duration'] }}@endif</td>
                                <td data-label="Qty">{{ $item['qty'] }}</td>
                                <td data-label="Price">{{ number_format($item['unit_price'], 2) }}</td>
                                <td data-label="Subtotal">{{ number_format($item['total'], 2) }}</td>
                                <td data-label="From">{{ $item['start'] ? date('d M Y, H:i', strtotime($item['start'])) : 'As agreed' }}</td>
                                <td data-label="To">{{ $item['end'] ? date('d M Y, H:i', strtotime($item['end'])) : 'As agreed' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">2</div><h3>Settling-In Time &amp; Extensions</h3></div>
            <p>You must allow enough time in your booking to accommodate settling in and wrap-up. The studio is used by many other clients. When your booked time ends, any request to add another hour (or further time) is <strong>subject to approval</strong> and availability — extension is not guaranteed.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">3</div><h3>Overtime Charges</h3></div>
            <p>Overtime beyond the booked end time is billed at <strong>12,000&nbsp;XAF for 0–60 minutes</strong> (or any part thereof), unless a longer extension is separately approved and priced. Overtime starts when your booked session ends.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">4</div><h3>Non-Refundable &amp; Generator Fuel</h3></div>
            <p>Studio rentals are <strong>not refundable</strong>. It is your responsibility to arrange generator fuel for power backup during your session.</p>
            <p>Generator fuel is charged at <strong>3,200&nbsp;XAF per hour</strong>. If you do not request this service and there is a power outage, the studio session will be lost and no refund or free remake is due.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">5</div><h3>Care of Studio &amp; Equipment</h3></div>
            <p>You agree to use the studio and any included equipment carefully and lawfully. Damage, loss, or misuse may be charged at repair or replacement cost. Leave the space tidy at the end of your session.</p>
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
            <p>By signing below, the client confirms they have read this Studio Rental Agreement, accept the session schedule and rates above (including overtime and generator fuel terms), and authorize identity verification via ID card upload.</p>
        </div>
    </div>

    <form id="sign-form" method="POST" action="{{ route('rental.agreement.sign', $contract->signature_token) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="signature_image" id="signature_image">
        <input type="hidden" name="agreement_read_confirmed" id="agreement_read_confirmed" value="0">

        <div class="signature-box">
            <h4>⚠ Signature Required</h4>
            <p>A digital signature and valid ID card are required to complete this studio rental agreement.</p>
            <button type="button" class="btn btn-outline" id="open-signature-modal" disabled>Add Signature</button>
            <img id="signature-preview" class="preview-signature" alt="Signature preview">

            @include('booking.partials.agreement_id_card_fields')
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="agreement_accepted" id="agreement_accepted" value="1" disabled>
            <label for="agreement_accepted">I have read and agree to the Studio Rental Agreement and confirm that all information provided is accurate.</label>
        </div>
        <p style="font-size:13px;margin:8px 0 0;color:#c9d4e8;">Tick the box above, then tap <strong>Submit Agreement</strong> at the bottom.</p>
        <noscript>
            <button type="submit" class="btn btn-accent" style="margin-top:12px;">✓ Submit Agreement</button>
        </noscript>
    </form>
</div>

<div class="footer-bar">
    <div class="footer-inner">
        <div>Do you accept the terms of this studio rental agreement?</div>
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
