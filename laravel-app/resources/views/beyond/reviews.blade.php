@extends('beyond.layout')

@section('title', 'Reviews · OGERA Agency')
@section('meta_description', 'What clients say about working with OGERA Agency, and a form to share your own experience.')

@push('head')
<style>
    .ogera-reviews { background:#f7f4ea; padding:60px 0 80px; }
    .ogera-reviews__wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
    .ogera-reviews__summary { display:flex; flex-wrap:wrap; gap:32px; align-items:center; background:#fff; padding:22px 26px;
        border-radius:14px; box-shadow: 0 2px 14px rgba(3,61,46,.08); margin-bottom: 30px; }
    .ogera-reviews__score { font-family: var(--og-serif); font-size: 3rem; color: var(--og-forest); line-height:1; font-weight:600; }
    .ogera-reviews__score small { display:block; font-size:.9rem; color:#64748b; font-family: var(--og-sans); margin-top:6px; letter-spacing:.02em; }
    .ogera-reviews__stars-lg { font-size: 1.5rem; color:#e0a72a; letter-spacing:2px; }
    .ogera-reviews__bars { flex:1; min-width:220px; }
    .ogera-reviews__bar { display:flex; align-items:center; gap:10px; font-size:12px; color:#4b5563; margin:3px 0; }
    .ogera-reviews__bar-track { flex:1; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden; }
    .ogera-reviews__bar-fill { height:100%; background: linear-gradient(90deg, #e0a72a, #c88a1d); }
    .ogera-reviews__grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:20px; }
    .ogera-review-card { background:#fff; border-radius:14px; padding: 22px 24px; box-shadow: 0 1px 8px rgba(3,61,46,.08); display:flex; flex-direction:column; }
    .ogera-review-card__stars { color:#e0a72a; letter-spacing:2px; margin-bottom:8px; font-size:1rem; }
    .ogera-review-card__title { font-family: var(--og-serif); font-size: 1.35rem; color: var(--og-forest); margin: 0 0 10px; font-weight: 600; }
    .ogera-review-card__body { color:#374151; line-height:1.6; font-size:.97rem; white-space: pre-wrap; }
    .ogera-review-card__meta { margin-top:14px; padding-top:14px; border-top:1px solid #f1eadf; display:flex; justify-content:space-between; align-items:baseline; gap:12px; }
    .ogera-review-card__name { font-weight:600; color:#1f2937; }
    .ogera-review-card__country { color:#6b7280; font-size:.85rem; }
    .ogera-review-card__reply { margin-top: 14px; padding: 12px 14px; background:#eef7f2; border-left:3px solid var(--og-forest); border-radius: 8px; font-size:.9rem; color:#12433a; }
    .ogera-review-card__reply strong { color: var(--og-forest); display:block; margin-bottom:2px; }
    .ogera-review-card.is-pinned { border: 2px solid #e0a72a; }
    .ogera-review-form { background:#fff; border-radius:14px; padding: 28px 32px; margin-top: 46px; box-shadow: 0 2px 14px rgba(3,61,46,.08); }
    .ogera-review-form h2 { font-family: var(--og-serif); font-size: 2rem; color: var(--og-forest); margin: 0 0 8px; }
    .ogera-review-form p.lead { color:#4b5563; margin-bottom: 22px; }
    .ogera-review-form label { display:block; font-weight:600; color:#1f2937; margin: 12px 0 6px; font-size:.9rem; }
    .ogera-review-form input, .ogera-review-form textarea, .ogera-review-form select {
        width:100%; padding: 10px 12px; border:1px solid #d1d5db; border-radius:8px;
        font-size: 15px; background:#fff; color:#111827; font-family: inherit;
    }
    .ogera-review-form input:focus, .ogera-review-form textarea:focus {
        outline:none; border-color: var(--og-forest); box-shadow: 0 0 0 3px rgba(3,61,46,.08);
    }
    .ogera-review-form textarea { min-height: 110px; resize: vertical; }
    .ogera-review-form .grid { display:grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 640px) { .ogera-review-form .grid { grid-template-columns: 1fr; } }
    .ogera-review-form .rating-picker { display:flex; gap:6px; padding:6px 0; }
    .ogera-review-form .rating-picker button {
        border:0; background:transparent; cursor:pointer; font-size: 30px; line-height: 1;
        color:#d1d5db; padding: 2px 4px; transition: color .12s;
    }
    .ogera-review-form .rating-picker button.is-active,
    .ogera-review-form .rating-picker button:hover,
    .ogera-review-form .rating-picker button:hover ~ button { color: #d1d5db; }
    .ogera-review-form .rating-picker.filled button { color:#d1d5db; }
    .ogera-review-form .rating-picker.filled button.is-lit { color:#e0a72a; }
    .ogera-review-form .submit-row { display:flex; justify-content:space-between; align-items:center; margin-top: 24px; gap: 14px; flex-wrap:wrap; }
    .ogera-review-form .hint { color:#6b7280; font-size:.85rem; }
    .ogera-review-form button.submit {
        background: var(--og-forest); color:#fff; border:0; padding: 12px 30px; border-radius: 999px;
        font-weight:600; font-size: 1rem; cursor:pointer; letter-spacing:.02em;
    }
    .ogera-review-form button.submit:hover { background:#022c22; }
    /* Honeypot */
    .ogera-review-form .honeypot { position:absolute; left:-9999px; height:0; width:0; overflow:hidden; }
    .ogera-review-empty { background:#fff; padding: 46px 22px; border-radius:14px; text-align:center; color:#6b7280; }
</style>
@endpush

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::text('reviews.hero_title', 'What Our Clients <em>Say</em>'),
    'subtitle' => \App\Support\SiteContent::text('reviews.hero_subtitle', 'Straight from the businesses, event organisers and event guests we have served.'),
])

<section class="ogera-reviews">
    <div class="ogera-reviews__wrap">
        @if(session('message'))
            <div class="ogera-review-card" style="margin-bottom: 22px; background:#eef7f2; border-left:4px solid var(--og-forest);">
                {{ session('message') }}
            </div>
        @endif
        @if($errors->any())
            <div class="ogera-review-card" style="margin-bottom: 22px; background:#fef2f2; border-left:4px solid #b91c1c; color:#7f1d1d;">
                {{ $errors->first() }}
            </div>
        @endif

        <div style="margin-bottom: 22px;">
            <h2 style="font-family: var(--og-serif); font-size: 2.2rem; color:#fff; color: var(--og-forest); margin:0 0 6px;">{{ $headline }}</h2>
            <p style="color:#4b5563; margin:0;">{{ $subtext }}</p>
        </div>

        @if($summary['count'] > 0)
            @php
                $avg = $summary['average'];
                $full = (int) floor($avg);
                $half = ($avg - $full) >= 0.5;
                $stars = str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', 5 - $full - ($half ? 1 : 0));
            @endphp
            <div class="ogera-reviews__summary">
                <div>
                    <div class="ogera-reviews__score">{{ number_format($avg, 1) }}<small>average of {{ $summary['count'] }} {{ \Illuminate\Support\Str::plural('review', $summary['count']) }}</small></div>
                    <div class="ogera-reviews__stars-lg" title="{{ $avg }} out of 5">{{ $stars }}</div>
                </div>
                <div class="ogera-reviews__bars">
                    @foreach([5,4,3,2,1] as $n)
                        @php $pct = $summary['count'] > 0 ? round($summary['distribution'][$n] * 100 / $summary['count']) : 0; @endphp
                        <div class="ogera-reviews__bar">
                            <span style="width: 26px;">{{ $n }} ★</span>
                            <span class="ogera-reviews__bar-track"><span class="ogera-reviews__bar-fill" style="width: {{ $pct }}%;"></span></span>
                            <span style="width: 36px; text-align:right;">{{ $summary['distribution'][$n] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="ogera-reviews__grid">
                @foreach($reviews as $review)
                    <div class="ogera-review-card {{ $review->is_pinned ? 'is-pinned' : '' }}">
                        <div class="ogera-review-card__stars" title="{{ $review->rating }} out of 5">
                            {{ str_repeat('★', (int) $review->rating) }}<span style="color:#e5e7eb;">{{ str_repeat('★', 5 - (int) $review->rating) }}</span>
                        </div>
                        @if($review->title)
                            <h3 class="ogera-review-card__title">{{ $review->title }}</h3>
                        @endif
                        <div class="ogera-review-card__body">{{ $review->message }}</div>
                        <div class="ogera-review-card__meta">
                            <div>
                                <div class="ogera-review-card__name">{{ $review->name }}</div>
                                @if($review->country)
                                    <div class="ogera-review-card__country">{{ $review->country }}</div>
                                @endif
                            </div>
                            <div class="ogera-review-card__country">{{ optional($review->created_at)->format('M Y') }}</div>
                        </div>
                        @if($review->admin_reply)
                            <div class="ogera-review-card__reply">
                                <strong>OGERA Agency replied</strong>
                                {{ $review->admin_reply }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="ogera-review-empty">
                <p style="font-size:1.1rem; margin: 0 0 6px;">No reviews yet — be the first to share your experience.</p>
                <p style="margin:0;">Use the form below.</p>
            </div>
        @endif

        <div class="ogera-review-form" id="form">
            <h2>Share your experience</h2>
            <p class="lead">All fields except phone and country are needed. Reviews of {{ $holdBelow }} stars or below are held for review before appearing on the site.</p>

            <form method="POST" action="{{ route('beyond.reviews.store') }}" novalidate>
                @csrf
                {{-- Honeypot: real people leave this empty. --}}
                <div class="honeypot" aria-hidden="true">
                    <label for="rv_website">Website</label>
                    <input type="text" name="website" id="rv_website" tabindex="-1" autocomplete="off">
                </div>

                <label>Your rating</label>
                <div class="rating-picker filled" data-rating>
                    @foreach([1,2,3,4,5] as $s)
                        <button type="button" data-star="{{ $s }}" aria-label="{{ $s }} star">★</button>
                    @endforeach
                </div>
                <input type="hidden" name="rating" id="rv_rating" value="{{ old('rating', 5) }}">

                <div class="grid">
                    <div>
                        <label for="rv_name">Your name</label>
                        <input type="text" name="name" id="rv_name" required value="{{ old('name', request('name')) }}">
                    </div>
                    <div>
                        <label for="rv_country">Country <span class="hint">(optional)</span></label>
                        <input type="text" name="country" id="rv_country" value="{{ old('country', request('country')) }}">
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <label for="rv_email">Email <span class="hint">(optional, kept private)</span></label>
                        <input type="email" name="email" id="rv_email" value="{{ old('email', request('email')) }}">
                    </div>
                    <div>
                        <label for="rv_phone">Phone <span class="hint">(optional)</span></label>
                        <input type="tel" name="phone" id="rv_phone" value="{{ old('phone', request('phone')) }}">
                    </div>
                </div>

                <label for="rv_title">Headline <span class="hint">(optional)</span></label>
                <input type="text" name="title" id="rv_title" placeholder="e.g. Delivered on time and beyond expectations" value="{{ old('title') }}">

                <label for="rv_message">Your review</label>
                <textarea name="message" id="rv_message" required placeholder="What went well, what could have been better…">{{ old('message') }}</textarea>

                <input type="hidden" name="reference" value="{{ old('reference', request('reference')) }}">

                <div class="submit-row">
                    <span class="hint">By submitting, you agree we may show your name and country next to your review.</span>
                    <button type="submit" class="submit">Send review</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    var picker = document.querySelector('[data-rating]');
    if (!picker) return;
    var hidden = document.getElementById('rv_rating');

    function paint(rating) {
        picker.querySelectorAll('button').forEach(function (btn) {
            var s = parseInt(btn.getAttribute('data-star'), 10);
            btn.classList.toggle('is-lit', s <= rating);
        });
    }

    picker.querySelectorAll('button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var s = parseInt(btn.getAttribute('data-star'), 10);
            hidden.value = s;
            paint(s);
        });
        btn.addEventListener('mouseover', function () {
            paint(parseInt(btn.getAttribute('data-star'), 10));
        });
    });
    picker.addEventListener('mouseleave', function () {
        paint(parseInt(hidden.value || '5', 10));
    });

    paint(parseInt(hidden.value || '5', 10));

    // If the URL was #form?ref=...&name=..., copy those into the fields.
    var hash = window.location.hash || '';
    var qIdx = hash.indexOf('?');
    if (qIdx !== -1) {
        var params = new URLSearchParams(hash.substring(qIdx + 1));
        ['name', 'email', 'phone', 'country', 'reference'].forEach(function (key) {
            var val = params.get(key);
            if (!val) return;
            var field = document.querySelector('[name="' + key + '"]');
            if (field && !field.value) field.value = val;
        });
    }
})();
</script>
@endpush
