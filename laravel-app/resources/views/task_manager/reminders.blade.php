@extends('layout.main')

@section('content')
@php $tmTab = 'tasks.reminders'; @endphp
<section class="forms">
    <div class="container-fluid tm-shell">
        @include('task_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="tm-title"><i class="dripicons-clock"></i> Task Reminders</h1>
            <p class="tm-subtitle">Pending WhatsApp reminders only. Sent reminders leave this list automatically. Delete a row to stop it from firing.</p>
        </div>
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif
        <form method="POST" action="{{ route('tasks.reminders.bulk_delete') }}" id="tm-reminders-bulk" onsubmit="return tmConfirmBulk(this, 'Delete selected reminders? They will not fire.');">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                <label class="mb-0 small text-muted">
                    <input type="checkbox" id="tm-reminders-all" class="mr-1"> Select all
                </label>
                <button type="submit" class="btn btn-sm btn-outline-danger" id="tm-reminders-delete" disabled>
                    <i class="dripicons-trash"></i> Delete selected
                </button>
            </div>
            <div class="tm-page-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Task</th>
                                <th>Priority</th>
                                <th>Reminder Time</th>
                                <th>Task Deadline</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reminders as $reminder)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $reminder->id }}" class="tm-reminder-check">
                                    </td>
                                    <td><strong>{{ optional($reminder->task)->title ?: '—' }}</strong></td>
                                    <td><span class="badge badge-warning">{{ optional($reminder->task)->priority }}</span></td>
                                    <td>{{ optional($reminder->reminder_time)->format('M d, Y H:i') }}</td>
                                    <td>
                                        {{ optional(optional($reminder->task)->deadline)->format('M d, Y') }}
                                        {{ optional($reminder->task)->deadline_time ? substr($reminder->task->deadline_time, 0, 5) : '' }}
                                    </td>
                                    <td><span class="badge badge-secondary">Pending</span></td>
                                    <td>
                                        <button type="submit" form="tm-reminder-one-{{ $reminder->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this reminder? It will not fire.');">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No pending reminders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $reminders->links() }}</div>
            </div>
        </form>
        @foreach($reminders as $reminder)
            <form id="tm-reminder-one-{{ $reminder->id }}" method="POST" action="{{ route('tasks.reminders.delete', $reminder->id) }}" class="d-none">@csrf</form>
        @endforeach
    </div>
</section>
<script>
(function () {
    function tmConfirmBulk(form, msg) {
        var n = form.querySelectorAll('.tm-reminder-check:checked').length;
        if (!n) { alert('Select at least one reminder.'); return false; }
        return confirm(msg);
    }
    window.tmConfirmBulk = tmConfirmBulk;
    var all = document.getElementById('tm-reminders-all');
    var btn = document.getElementById('tm-reminders-delete');
    var boxes = function () { return document.querySelectorAll('.tm-reminder-check'); };
    function sync() {
        var checked = document.querySelectorAll('.tm-reminder-check:checked').length;
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
