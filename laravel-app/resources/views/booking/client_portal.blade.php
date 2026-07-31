<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Portal - {{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" />
    <link rel="stylesheet" href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}">
    <style>
        body { background: #f3f6fb; font-family: "Nunito", sans-serif; margin: 0; }
        .portal-header {
            background: linear-gradient(180deg, #041f4a 0%, #033d2e 100%);
            color: #fff;
            padding: 28px 0 22px;
            text-align: center;
        }
        .portal-header h2 { margin: 8px 0 4px; font-size: 1.6rem; }
        .portal-header p { margin: 0; opacity: 0.9; }
        .portal-card {
            background: #fff; border-radius: 14px; padding: 20px;
            box-shadow: 0 2px 12px rgba(3,61,46,0.08); margin-bottom: 18px;
        }
        .badge-signed { background: #c6ab47; color: #071711; }
        .success-banner {
            background: #e8f8ef;
            border: 1px solid #b7e4c7;
            color: #0b3d2c;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
<div class="portal-header">
    <div class="container">
        @include('booking.partials.agreement_brand', ['compact' => true])
        <h2>{{ \App\Support\SiteBrand::siteTitle($general_setting ?? null) }} Client Portal</h2>
        <p>Welcome, {{ $customer->name }}</p>
    </div>
</div>

<div class="container py-4">
    @if(session()->has('message'))
        <div class="success-banner">
            <strong>Agreement submitted successfully.</strong>
            <div class="mt-1">{{ session()->get('message') }}</div>
        </div>
    @endif
    @if(session()->has('not_permitted'))
        <div class="alert alert-danger">{{ session()->get('not_permitted') }}</div>
    @endif

    @if($contract->signed_at)
        <div class="portal-card">
            @if($contract->isPendingReview())
                <span class="badge badge-warning p-2">Awaiting Admin Review</span>
                <p class="mt-3 mb-0">You signed on {{ $contract->signed_at->format('d M Y, H:i') }}. Our team will countersign and send your final PDF and QR code via WhatsApp shortly.</p>
            @elseif($contract->isApproved())
                <span class="badge badge-signed p-2">Agreement Approved</span>
                <p class="mt-3 mb-0">Signed on {{ $contract->signed_at->format('d M Y, H:i') }} · Approved {{ optional($contract->approved_at)->format('d M Y, H:i') }}</p>
            @else
                <span class="badge badge-signed p-2">Agreement Signed</span>
                <p class="mt-3 mb-0">Signed on {{ $contract->signed_at->format('d M Y, H:i') }}</p>
            @endif
            @if($contract->client_username && $contract->generated_password)
                <p class="mt-2 mb-0"><strong>Login username:</strong> {{ $contract->client_username }}</p>
                <p class="mb-0"><strong>Temporary password:</strong> {{ $contract->generated_password }}</p>
            @endif
        </div>
    @endif

    <div class="portal-card">
        <h4>{{ ($contract->contract_type ?? '') === 'software_license' ? 'Your Subscriptions' : (($contract->contract_type ?? '') === 'studio_rental' ? 'Your Studio Sessions' : 'Your Rentals') }}</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>{{ ($contract->contract_type ?? '') === 'software_license' ? 'Products' : 'Equipment' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $rental)
                        <tr>
                            <td>{{ $rental->reference_no }}</td>
                            <td>{{ optional($rental->created_at)->format('d M Y') }}</td>
                            <td>
                                @if($rental->booking_status == 1) Completed
                                @elseif($rental->booking_status == 2) Pending
                                @elseif($rental->booking_status == 3) Returned
                                @else Draft @endif
                            </td>
                            <td>{{ number_format($rental->grand_total, 2) }}</td>
                            <td>
                                @foreach(($rental->bookingProduct ?? []) as $line)
                                    {{ optional($line->product)->name ?? 'Item' }} (x{{ $line->qty }})<br>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($contract->signed_at)
        <div class="portal-card">
            <h4>Create Your Login Credentials</h4>
            <p class="text-muted">You can keep the phone-number login or set a custom username and password.</p>
            <form method="POST" action="{{ route('rental.portal.credentials', $token) }}">
                @csrf
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="{{ $contract->client_username }}" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Save Credentials</button>
            </form>
        </div>
    @endif
</div>
</body>
</html>
