@extends('layout.main')

@section('content')
@php $tmTab = 'tasks.scheduled'; @endphp
<section class="forms">
    <div class="container-fluid tm-shell">
        @include('task_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="tm-title">Scheduled Tasks</h1>
            <p class="tm-subtitle">Tasks waiting to send (Africa/Kigali). After they send they leave this list. Cancel selected items to stop them from firing.</p>
        </div>
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif
        <form method="POST" action="{{ route('tasks.scheduled.bulk_cancel') }}" id="tm-scheduled-bulk" onsubmit="return tmConfirmScheduled(this);">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                <label class="mb-0 small text-muted">
                    <input type="checkbox" id="tm-scheduled-all" class="mr-1"> Select all
                </label>
                <button type="submit" class="btn btn-sm btn-outline-danger" id="tm-scheduled-delete" disabled>
                    <i class="dripicons-trash"></i> Cancel selected
                </button>
            </div>
            <div class="tm-page-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Subject</th>
                                <th>Send at</th>
                                <th>Assignees</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $task->id }}" class="tm-scheduled-check"></td>
                                    <td><strong>{{ $task->title }}</strong></td>
                                    <td>{{ optional($task->scheduled_for)->format('d M Y H:i') ?: '—' }}</td>
                                    <td>{{ $task->assignments->count() }}</td>
                                    <td>{{ $task->priority }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No scheduled tasks.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($tasks, 'links'))
                    <div class="mt-3">{{ $tasks->links() }}</div>
                @endif
            </div>
        </form>
    </div>
</section>
<script>
(function () {
    window.tmConfirmScheduled = function (form) {
        var n = form.querySelectorAll('.tm-scheduled-check:checked').length;
        if (!n) { alert('Select at least one scheduled task.'); return false; }
        return confirm('Cancel ' + n + ' scheduled send(s)? They will not fire.');
    };
    var all = document.getElementById('tm-scheduled-all');
    var btn = document.getElementById('tm-scheduled-delete');
    var boxes = function () { return document.querySelectorAll('.tm-scheduled-check'); };
    function sync() {
        var checked = document.querySelectorAll('.tm-scheduled-check:checked').length;
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
