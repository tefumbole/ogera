<style>
    .rv-tabbar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
    .rv-tabbar button { border:1px solid #d1d5db; background:#fff; padding:8px 14px; border-radius:20px; font-size:13px; cursor:pointer; color:#374151; }
    .rv-tabbar button.is-active { background:#033d2e; color:#fff; border-color:#033d2e; }
    .rv-panel { display:none; }
    .rv-panel.is-active { display:block; }
    .rv-list { display:grid; gap:12px; }
    .rv-card { border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding: 16px 18px; }
    .rv-card.is-pending { border-left: 4px solid #f59e0b; background: #fffbeb; }
    .rv-card.is-public { border-left: 4px solid #10b981; }
    .rv-card.is-pinned { box-shadow: 0 0 0 2px #e0a72a inset; }
    .rv-card__top { display:flex; justify-content:space-between; align-items:baseline; gap:12px; flex-wrap:wrap; margin-bottom:6px; }
    .rv-card__who { font-weight:600; color:#111827; }
    .rv-card__stars { color:#e0a72a; letter-spacing:2px; }
    .rv-card__stars .dim { color:#e5e7eb; }
    .rv-card__meta { color:#6b7280; font-size:12px; }
    .rv-card__title { margin: 4px 0 6px; font-weight:600; color:#033d2e; }
    .rv-card__body { color:#374151; white-space: pre-wrap; margin: 0 0 10px; font-size:14px; }
    .rv-card__actions { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
    .rv-card__actions form { display:inline; }
    .rv-card__actions .btn { font-size:12px; padding: 4px 10px; }
    .rv-card__reply { background:#eef7f2; border-left:3px solid #033d2e; padding:8px 12px; border-radius:6px; font-size:13px; color:#12433a; margin-bottom:8px; }
    .rv-edit { display:none; padding-top: 10px; border-top: 1px dashed #e5e7eb; margin-top: 10px; }
    .rv-edit.is-open { display:block; }
    .rv-edit .form-group { margin-bottom: 8px; }
    .rv-settings-card { background:#f7f4ea; border-radius:12px; padding: 18px 22px; margin-bottom: 20px; }
    .rv-star-picker button { border:0; background:transparent; font-size: 26px; color:#e5e7eb; cursor:pointer; padding: 0 2px; }
    .rv-star-picker button.is-lit { color:#e0a72a; }
    .rv-add-form .grid { display:grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    @media (max-width: 720px) { .rv-add-form .grid { grid-template-columns: 1fr; } }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
    <div>
        <h5 class="mb-1"><i class="dripicons-star"></i> Reviews</h5>
        <p class="text-muted mb-0" style="font-size:13px;">
            Moderate submissions, edit or hide reviews, and control the "Review us" link on
            the public site, invoices, letters and WhatsApp messages.
        </p>
    </div>
    <a href="{{ $reviewsSettings['public_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="dripicons-preview"></i> View public page
    </a>
</div>

{{-- Feature settings ---------------------------------------------------- --}}
<form method="POST" action="{{ route('reviews.settings') }}" class="rv-settings-card">
    @csrf
    <div class="row">
        <div class="col-md-4 form-group">
            <label class="d-flex align-items-center" style="gap:8px;">
                <input type="checkbox" name="enabled" value="1" {{ $reviewsSettings['enabled'] ? 'checked' : '' }}>
                <span><strong>Reviews enabled</strong> — public page + form active</span>
            </label>
        </div>
        <div class="col-md-4 form-group">
            <label class="d-flex align-items-center" style="gap:8px;">
                <input type="checkbox" name="outbound_cta" value="1" {{ $reviewsSettings['outbound_cta'] ? 'checked' : '' }}>
                <span><strong>Add "Review us" link</strong> to invoices, letters and messages</span>
            </label>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold" for="rv-hold-below">Hold reviews below</label>
            <select id="rv-hold-below" name="hold_below" class="form-control form-control-sm" style="max-width: 200px;">
                @foreach([1,2,3,4,5] as $n)
                    <option value="{{ $n }}" {{ $reviewsSettings['hold_below'] == $n ? 'selected' : '' }}>{{ $n }} star{{ $n === 1 ? '' : 's' }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">Ratings below this go to the moderation queue.</small>
        </div>
        <div class="col-md-6 form-group">
            <label class="font-weight-bold" for="rv-headline">Public page headline</label>
            <input type="text" id="rv-headline" name="headline" class="form-control form-control-sm" value="{{ $reviewsSettings['headline'] }}">
        </div>
        <div class="col-md-6 form-group">
            <label class="font-weight-bold" for="rv-subtext">Public page subtext</label>
            <input type="text" id="rv-subtext" name="subtext" class="form-control form-control-sm" value="{{ $reviewsSettings['subtext'] }}">
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Save settings</button>
</form>

{{-- Filter tabs (Pending / Published / Add) ------------------------------ --}}
@php
    $pendingCount = $reviewsPending->count();
    $publicCount = $reviewsPublic->count();
@endphp
<div class="rv-tabbar" data-rv-tabs>
    <button type="button" class="{{ $pendingCount > 0 ? 'is-active' : '' }}" data-panel="pending">
        Pending moderation ({{ $pendingCount }})
    </button>
    <button type="button" class="{{ $pendingCount > 0 ? '' : 'is-active' }}" data-panel="published">
        Published ({{ $publicCount }})
    </button>
    <button type="button" data-panel="add">
        <i class="dripicons-plus"></i> Add manually
    </button>
</div>

{{-- Pending queue --}}
<div class="rv-panel {{ $pendingCount > 0 ? 'is-active' : '' }}" data-panel="pending">
    @if($pendingCount === 0)
        <p class="text-muted">Nothing waiting for moderation.</p>
    @else
        <p class="text-muted" style="font-size:13px;">Reviews at {{ $reviewsSettings['hold_below'] }} stars and under land here first. Publish to make them visible on the public page.</p>
        <div class="rv-list">
            @foreach($reviewsPending as $review)
                @include('site_content.partials.review_card', ['review' => $review])
            @endforeach
        </div>
    @endif
</div>

{{-- Published --}}
<div class="rv-panel {{ $pendingCount > 0 ? '' : 'is-active' }}" data-panel="published">
    @if($publicCount === 0)
        <p class="text-muted">No published reviews yet.</p>
    @else
        <div class="rv-list">
            @foreach($reviewsPublic as $review)
                @include('site_content.partials.review_card', ['review' => $review])
            @endforeach
        </div>
    @endif
</div>

{{-- Add manually --}}
<div class="rv-panel" data-panel="add">
    <form method="POST" action="{{ route('reviews.store') }}" class="rv-add-form">
        @csrf
        <div class="grid">
            <div class="form-group">
                <label class="font-weight-bold">Client name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Email <span class="text-muted">(private)</span></label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Country</label>
                <input type="text" name="country" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Rating <span class="text-danger">*</span></label>
            <div class="rv-star-picker" data-star-picker>
                @foreach([1,2,3,4,5] as $s)
                    <button type="button" data-star="{{ $s }}">★</button>
                @endforeach
            </div>
            <input type="hidden" name="rating" value="5" data-star-value>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Headline</label>
            <input type="text" name="title" class="form-control" placeholder="Short summary">
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Review <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Reference <span class="text-muted">(booking / invoice / letter number, optional)</span></label>
            <input type="text" name="reference" class="form-control">
        </div>
        <label class="d-flex align-items-center mb-2" style="gap:8px;">
            <input type="checkbox" name="is_public" value="1" checked> Publish immediately
        </label>
        <label class="d-flex align-items-center mb-3" style="gap:8px;">
            <input type="checkbox" name="is_pinned" value="1"> Pin to the top of the public page
        </label>
        <button type="submit" class="btn btn-primary btn-sm">Add review</button>
    </form>
</div>

<script>
(function () {
    var tabbar = document.querySelector('[data-rv-tabs]');
    if (tabbar) {
        var buttons = tabbar.querySelectorAll('button');
        var panels = document.querySelectorAll('.rv-panel');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-panel');
                buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                panels.forEach(function (p) {
                    p.classList.toggle('is-active', p.getAttribute('data-panel') === target);
                });
            });
        });
    }

    document.querySelectorAll('[data-star-picker]').forEach(function (picker) {
        var hidden = picker.parentElement.querySelector('[data-star-value]');
        function paint(v) {
            picker.querySelectorAll('button').forEach(function (b) {
                b.classList.toggle('is-lit', parseInt(b.getAttribute('data-star'), 10) <= v);
            });
        }
        picker.querySelectorAll('button').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = parseInt(b.getAttribute('data-star'), 10);
                if (hidden) hidden.value = v;
                paint(v);
            });
        });
        paint(parseInt(hidden ? hidden.value : '5', 10));
    });

    document.querySelectorAll('.rv-edit-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = document.getElementById(btn.getAttribute('data-target'));
            if (wrap) wrap.classList.toggle('is-open');
        });
    });
})();
</script>
