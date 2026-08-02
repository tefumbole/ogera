@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
            <div>
                <h3 class="mb-1" style="color:#033d2e;font-weight:800;"><i class="dripicons-checklist"></i> Testing Guide</h3>
                <p class="text-muted mb-0">
                    Checklist for this build, {{ $version }}@if($updatedAt) &middot; guide last updated {{ $updatedAt }}@endif.
                    Your ticks are saved in this browser only.
                </p>
            </div>
            <div class="d-flex align-items-center tg-actions" style="gap:8px;">
                <span class="badge badge-pill tg-progress" id="tg-progress">0 of 0</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="tg-reset"><i class="dripicons-clockwise"></i> Reset ticks</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="dripicons-print"></i> Print</button>
            </div>
        </div>

        @if($html === null)
            <div class="alert alert-warning">
                The guide file is missing from this deployment
                (<code>{{ \App\Http\Controllers\TestingGuideController::SOURCE }}</code>).
            </div>
        @else
            <div class="card">
                <div class="card-body tg-doc" id="tg-doc">
                    {!! $html !!}
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@section('scripts')
<style>
    .tg-progress { background: #033d2e; color: #fff; font-size: 13px; padding: 7px 12px; }
    .tg-doc { max-width: 900px; line-height: 1.7; color: #23324d; }
    .tg-doc h1 { font-size: 26px; font-weight: 800; color: #033d2e; margin: 0 0 6px; }
    .tg-doc h2 {
        font-size: 20px; font-weight: 800; color: #033d2e;
        margin: 34px 0 12px; padding-top: 16px; border-top: 2px solid #eef2f7;
    }
    .tg-doc h3 { font-size: 16px; font-weight: 700; color: #1f2a44; margin: 22px 0 8px; }
    .tg-doc p { margin: 0 0 12px; }
    .tg-doc code {
        background: #f3f6fb; color: #b0357a; padding: 2px 5px;
        border-radius: 4px; font-size: 13px;
    }
    .tg-doc pre {
        background: #02261c; color: #e8efff; padding: 14px 16px;
        border-radius: 10px; overflow-x: auto; margin: 0 0 14px;
    }
    .tg-doc pre code { background: none; color: inherit; padding: 0; }
    .tg-doc table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
    .tg-doc th, .tg-doc td { border: 1px solid #e3e9f2; padding: 8px 10px; font-size: 14px; text-align: left; }
    .tg-doc th { background: #f6f9fc; color: #033d2e; font-weight: 700; }
    .tg-doc blockquote { border-left: 4px solid #c6ab47; margin: 0 0 14px; padding: 4px 0 4px 14px; color: #5b6880; }
    .tg-doc hr { border: 0; border-top: 2px solid #eef2f7; margin: 28px 0; }

    /* Task lists: one tappable row per check. */
    .tg-doc ul.contains-task-list, .tg-doc ol.contains-task-list { list-style: none; padding-left: 0; }
    .tg-doc li.task-list-item {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 9px 12px; margin-bottom: 6px;
        border: 1px solid #e6ecf5; border-radius: 8px; background: #fff;
    }
    .tg-doc li.task-list-item input[type="checkbox"] {
        width: 20px; height: 20px; flex: 0 0 20px; margin-top: 1px; cursor: pointer;
    }
    .tg-doc li.task-list-item.is-done { background: #f1fbf6; border-color: #bfe3d0; }
    .tg-doc li.task-list-item.is-done > * { color: #6b7c8f; }

    @media print {
        .side-navbar, .navbar, .footer, #beyond-module-tabs, .tg-actions { display: none !important; }
        .page-content, .content-inner { margin: 0 !important; padding: 0 !important; }
        .tg-doc { max-width: none; }
        .card { border: 0 !important; box-shadow: none !important; }
    }
</style>
<script>
(function () {
    var doc = document.getElementById('tg-doc');
    if (!doc) return;

    // Keyed by build, so a guide that gains or loses checks starts clean rather
    // than restoring ticks against the wrong lines.
    var KEY = 'ogera.testing-guide.' + @json($version);
    var boxes = [].slice.call(doc.querySelectorAll('li > input[type="checkbox"]'));
    var progress = document.getElementById('tg-progress');

    // CommonMark emits a bare checkbox with no classes, so tag the rows here.
    boxes.forEach(function (box) {
        var item = box.parentNode;
        item.classList.add('task-list-item');
        if (item.parentNode) item.parentNode.classList.add('contains-task-list');
    });

    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) {}

    function render() {
        var done = 0;
        boxes.forEach(function (box) {
            var item = box.closest('li');
            if (box.checked) done++;
            if (item) item.classList.toggle('is-done', box.checked);
        });
        if (progress) progress.textContent = done + ' of ' + boxes.length;
    }

    function save() {
        var state = {};
        boxes.forEach(function (box, i) { if (box.checked) state[i] = 1; });
        try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {}
    }

    boxes.forEach(function (box, i) {
        box.disabled = false;
        box.checked = !!saved[i];
        box.addEventListener('change', function () { render(); save(); });
    });

    // Tapping the text of a row toggles it too, which is easier on a phone.
    doc.addEventListener('click', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'A') return;
        var item = e.target.closest ? e.target.closest('li.task-list-item') : null;
        if (!item) return;
        var box = item.querySelector('input[type="checkbox"]');
        if (!box) return;
        box.checked = !box.checked;
        render();
        save();
    });

    var reset = document.getElementById('tg-reset');
    if (reset) {
        reset.addEventListener('click', function () {
            if (!confirm('Clear every tick on this checklist?')) return;
            boxes.forEach(function (box) { box.checked = false; });
            render();
            save();
        });
    }

    render();
})();
</script>
@endsection
