@extends('layout.main')

@section('content')
@php $anTab = 'announcements.scheduled'; @endphp
<section class="forms">
    <div class="container-fluid an-shell">
        @include('announcement_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="an-title"><i class="dripicons-clock"></i> Scheduled Announcements</h1>
            <p class="an-subtitle">Waiting to send (Africa/Kigali). After they send they leave this list. Cancel selected items to stop them from firing.</p>
        </div>
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif
        <form method="POST" action="{{ route('announcements.scheduled.bulk_cancel') }}" onsubmit="return anConfirmScheduled(this);">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                <label class="mb-0 small text-muted">
                    <input type="checkbox" id="an-scheduled-all" class="mr-1"> Select all
                </label>
                <button type="submit" class="btn btn-sm btn-outline-danger" id="an-scheduled-delete" disabled>
                    <i class="dripicons-trash"></i> Cancel selected
                </button>
            </div>
            <div class="an-page-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Reference</th>
                                <th>Subject</th>
                                <th>Send at</th>
                                <th>Recipients</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="an-scheduled-check"></td>
                                    <td><code>{{ $item->reference }}</code></td>
                                    <td><strong>{{ $item->subject }}</strong></td>
                                    <td>{{ optional($item->scheduled_for)->format('d M Y H:i') ?: '—' }}</td>
                                    <td>{{ count($item->recipients()) }} (+{{ count($item->ccRecipients()) }} CC)</td>
                                    <td class="text-right">
                                        <a href="{{ route('announcements.compose', ['clone' => $item->id]) }}" class="an-btn-outline">Clone</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No scheduled announcements.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $items->links() }}</div>
            </div>
        </form>
    </div>
</section>
<script>
(function () {
    window.anConfirmScheduled = function (form) {
        var n = form.querySelectorAll('.an-scheduled-check:checked').length;
        if (!n) { alert('Select at least one scheduled announcement.'); return false; }
        return confirm('Cancel ' + n + ' scheduled send(s)? They will not fire.');
    };
    var all = document.getElementById('an-scheduled-all');
    var btn = document.getElementById('an-scheduled-delete');
    var boxes = function () { return document.querySelectorAll('.an-scheduled-check'); };
    function sync() {
        var checked = document.querySelectorAll('.an-scheduled-check:checked').length;
        if (btn) btn.disabled = checked === 0;
        if (all) {
            var total = boxes().length;
            all.checked = total > 0 && checked === total;
            all.indeterminate = checked > 0 && checked < total;
        }
    }
    if (all) all.addEventListener('change', function () {
        boxes().forEach(function (c) { c.checked = all.checked; });
        sync();
    });
    boxes().forEach(function (c) { c.addEventListener('change', sync); });
    sync();
})();
</script>
@endsection
