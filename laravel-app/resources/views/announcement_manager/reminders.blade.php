@extends('layout.main')

@section('content')
@php $anTab = 'announcements.reminders'; @endphp
<section class="forms">
    <div class="container-fluid an-shell">
        @include('announcement_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="an-title"><i class="dripicons-clock"></i> Announcement Reminders</h1>
            <p class="an-subtitle">Pending reminders only. Sent ones leave this list. Delete to stop a reminder from firing.</p>
        </div>
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif
        <form method="POST" action="{{ route('announcements.reminders.bulk_delete') }}" onsubmit="return anConfirmReminders(this);">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                <label class="mb-0 small text-muted">
                    <input type="checkbox" id="an-reminders-all" class="mr-1"> Select all
                </label>
                <button type="submit" class="btn btn-sm btn-outline-danger" id="an-reminders-delete" disabled>
                    <i class="dripicons-trash"></i> Delete selected
                </button>
            </div>
            <div class="an-page-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Announcement</th>
                                <th>Reminder Time</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reminders as $reminder)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $reminder->id }}" class="an-reminder-check"></td>
                                    <td>
                                        <strong>{{ optional($reminder->announcement)->subject ?: '—' }}</strong>
                                        <div class="small text-muted">{{ optional($reminder->announcement)->reference }}</div>
                                    </td>
                                    <td>{{ optional($reminder->reminder_time)->format('d M Y H:i') }}</td>
                                    <td><span class="an-badge scheduled">Pending</span></td>
                                    <td>
                                        <button type="submit" form="an-reminder-one-{{ $reminder->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this reminder? It will not fire.');">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No pending reminders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $reminders->links() }}</div>
            </div>
        </form>
        @foreach($reminders as $reminder)
            <form id="an-reminder-one-{{ $reminder->id }}" method="POST" action="{{ route('announcements.reminders.delete', $reminder->id) }}" class="d-none">@csrf</form>
        @endforeach
    </div>
</section>
<script>
(function () {
    window.anConfirmReminders = function (form) {
        var n = form.querySelectorAll('.an-reminder-check:checked').length;
        if (!n) { alert('Select at least one reminder.'); return false; }
        return confirm('Delete selected reminders? They will not fire.');
    };
    var all = document.getElementById('an-reminders-all');
    var btn = document.getElementById('an-reminders-delete');
    var boxes = function () { return document.querySelectorAll('.an-reminder-check'); };
    function sync() {
        var checked = document.querySelectorAll('.an-reminder-check:checked').length;
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
