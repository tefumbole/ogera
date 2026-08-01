<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Student Accommodation Agreement - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" />
    @include('booking.partials.agreement_styles')
</head>
<body>
<div class="wrap">
    <div class="hero">
        @include('booking.partials.agreement_brand')
        <h1>Student Accommodation Agreement</h1>
        <p>Booking Ref: <strong>{{ $booking->reference_no }}</strong> | Tenant: <strong>{{ $booking->customer->name ?? '' }}</strong></p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div id="agreement-content">
        <div class="card">
            <div class="card-head"><div class="num">1</div><h3>Room Assignment & Term</h3></div>
            <p>This agreement covers student accommodation at our facility. The tenant is assigned the room(s) listed below for the rental period shown. The room is a student facility and must be used solely for residential purposes during the agreed term.</p>
            <div class="table-wrap">
                <table class="equipment">
                    <thead>
                        <tr>
                            <th>Room / Unit</th>
                            <th>Code</th>
                            <th>Qty</th>
                            <th>Monthly Rent</th>
                            <th>Subtotal</th>
                            <th>Occupancy Until</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td data-label="Room / Unit">{{ $item['name'] }}</td>
                                <td data-label="Code">{{ $item['code'] }}</td>
                                <td data-label="Qty">{{ $item['qty'] }}</td>
                                <td data-label="Monthly Rent">{{ number_format($item['unit_price'], 2) }}</td>
                                <td data-label="Subtotal">{{ number_format($item['total'], 2) }}</td>
                                <td data-label="Occupancy Until">{{ $item['end'] ? date('d M Y', strtotime($item['end'])) : 'As scheduled' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">2</div><h3>Pre-Occupancy Inspection</h3></div>
            <p>Before you begin use of the room, you must inspect every item in the room and confirm that all fixtures, fittings, furniture, and equipment are in good working order. At checkout, every item will be inspected again. If you claim an item was defective or damaged but did not report it at move-in, you will be held responsible for repair or replacement costs.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">3</div><h3>Single Occupancy</h3></div>
            <p>This accommodation is for <strong>single occupancy only</strong> and is not intended for more than one person. Dual or multi-occupancy without prior written approval will incur an additional <strong>50% increase in rent</strong>, payable immediately upon discovery or upon approval of additional occupants.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">4</div><h3>Parking</h3></div>
            <p><strong>No parking space is available</strong> for tenants in this facility. Tenants must not park vehicles on the premises unless expressly authorized in writing by management.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">5</div><h3>Security Deposit — 25,000 FRS</h3></div>
            <p>A compulsory refundable deposit of <strong>25,000 FRS</strong> must be paid before occupancy. The deposit is refundable when you vacate the property, subject to inspection. If items in your room require repairs at exit, you will repair them and collect the deposit, or the deposit will be used for repairs. Any balance owed after repairs will be your responsibility; any surplus will be reimbursed to you.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">6</div><h3>Room Condition & Walls</h3></div>
            <p>Nails on walls, dirtying of walls, or unauthorized markings are not allowed. Repainting will be required at exit if walls are damaged or defaced, and the cost may be deducted from your deposit or charged separately.</p>
        </div>
        <div class="card">
            <div class="card-head"><div class="num">7</div><h3>Cleanliness & Windows</h3></div>
            <p>Throwing dirt or waste over windows or from the building is strictly prohibited. Tenants caught doing so will be required to clean the littered area, or part of the deposit will be used for professional cleaning.</p>
        </div>
        @if(!empty($booking->booking_note))
        <div class="card">
            <div class="card-head"><div class="num">8</div><h3>Additional Notes</h3></div>
            <div class="booking-note-content">{!! \App\Support\BookingNoteFormatter::forDisplay($booking->booking_note) !!}</div>
        </div>
        @endif
        <div class="card">
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '9' : '8' }}</div><h3>Payment Information</h3></div>
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
            <div class="card-head"><div class="num">{{ !empty($booking->booking_note) ? '10' : '9' }}</div><h3>Acceptance</h3></div>
            <p>By signing below, the tenant confirms they have read this Student Accommodation Agreement, accept all terms including the 25,000 FRS deposit and inspection requirements, and authorize identity verification via ID card upload.</p>
        </div>
    </div>

    <form id="sign-form" method="POST" action="{{ route('rental.agreement.sign', $contract->signature_token) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="signature_image" id="signature_image">
        <input type="hidden" name="agreement_read_confirmed" id="agreement_read_confirmed" value="0">

        <div class="signature-box">
            <h4>⚠ Signature Required</h4>
            <p>A digital signature and valid ID card are required to complete this accommodation agreement.</p>
            <button type="button" class="btn btn-outline" id="open-signature-modal" disabled>Add Signature</button>
            <img id="signature-preview" class="preview-signature" alt="Signature preview">

            @include('booking.partials.agreement_id_card_fields')
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="agreement_accepted" id="agreement_accepted" value="1" disabled>
            <label for="agreement_accepted">I have read and agree to the Student Accommodation Agreement and confirm that all information provided is accurate.</label>
        </div>
        <p style="font-size:13px;margin:8px 0 0;color:#c9d4e8;">Tick the box above, then tap <strong>Submit Agreement</strong> at the bottom.</p>
        <noscript>
            <button type="submit" class="btn btn-accent" style="margin-top:12px;">✓ Submit Agreement</button>
        </noscript>
    </form>
</div>

<div class="footer-bar">
    <div class="footer-inner">
        <div>Do you accept the terms outlined in this accommodation agreement?</div>
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
