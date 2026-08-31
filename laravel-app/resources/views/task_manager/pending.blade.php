@extends('layout.main')

@section('content')
@php $tmTab = 'tasks.pending'; @endphp
<section class="forms">
    <div class="container-fluid tm-shell">
        @include('task_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="tm-title">Pending Acceptances</h1>
            <p class="tm-subtitle">Assignments waiting for assignees to accept. Cancel selected items if someone takes too long — their WhatsApp invite link becomes invalid.</p>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif

        <form method="POST" action="{{ route('tasks.pending.bulk_delete') }}" id="tm-pending-bulk" onsubmit="return tmConfirmPending(this);">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                <label class="mb-0 small text-muted">
                    <input type="checkbox" id="tm-pending-all" class="mr-1" {{ $assignments->isEmpty() ? 'disabled' : '' }}> Select all
                </label>
                <button type="submit" class="btn btn-sm btn-outline-danger" id="tm-pending-delete" disabled>
                    <i class="dripicons-trash"></i> Cancel selected
                </button>
            </div>

            @forelse($assignments as $a)
                @php $u = $users->get($a->user_id); @endphp
                <div class="tm-page-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <label class="mb-0 d-flex align-items-center" style="gap:8px;cursor:pointer;">
                            <input type="checkbox" name="ids[]" value="{{ $a->id }}" class="tm-pending-check">
                            <span class="badge badge-warning">{{ optional($a->task)->priority }}</span>
                        </label>
                        <span class="badge badge-secondary">Pending</span>
                    </div>
                    <h5 class="mt-2 mb-1" style="color:#033d2e;">{{ optional($a->task)->title }}</h5>
                    <p class="mb-1"><i class="dripicons-user"></i> Assignee: {{ optional($u)->name }} — {{ optional($u)->phone }}</p>
                    <p class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags(optional($a->task)->description), 160) }}</p>
                    <div class="small text-muted">
                        Due: {{ optional(optional($a->task)->deadline)->format('M d, Y') ?: '—' }}
                    </div>
                    <hr>
                    <div class="small text-muted">Waiting for assignee to accept via WhatsApp link</div>
                </div>
            @empty
                <div class="tm-page-card text-center text-muted py-4">No pending acceptances.</div>
            @endforelse
        </form>
    </div>
</section>
<script>
(function () {
    window.tmConfirmPending = function (form) {
        var n = form.querySelectorAll('.tm-pending-check:checked').length;
        if (!n) { alert('Select at least one pending acceptance.'); return false; }
        return confirm('Cancel ' + n + ' pending acceptance(s)? Their WhatsApp invite links will become invalid.');
    };
    var all = document.getElementById('tm-pending-all');
    var btn = document.getElementById('tm-pending-delete');
    var boxes = function () { return document.querySelectorAll('.tm-pending-check'); };
    function sync() {
        var checked = document.querySelectorAll('.tm-pending-check:checked').length;
        if (btn) btn.disabled = checked === 0;
        if (all && !all.disabled) {
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
