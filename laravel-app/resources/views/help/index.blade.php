@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
            <div>
                <h3 class="mb-1" style="color:#033d2e;font-weight:800;"><i class="dripicons-question"></i> User Guide</h3>
                <p class="text-muted mb-0">
                    How to use Ogera, written for staff. Build {{ $version }}.
                    Core modules first — more chapters will be added over time.
                </p>
            </div>
            <a href="{{ route('setting.testingGuide') }}" class="btn btn-sm btn-outline-secondary">
                <i class="dripicons-checklist"></i> Testing Guide
            </a>
        </div>

        <div class="row">
            @foreach($chapters as $slug => $chapter)
                <div class="col-md-6 col-lg-4 mb-3">
                    <a href="{{ route('help.show', $slug) }}" class="help-card">
                        <div class="help-card-icon"><i class="{{ $chapter['icon'] }}"></i></div>
                        <div>
                            <h4>{{ $chapter['title'] }}</h4>
                            <p>{{ $chapter['summary'] }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@section('scripts')
<style>
    .help-card {
        display: flex; gap: 14px; align-items: flex-start;
        height: 100%; padding: 18px 16px; border-radius: 12px;
        border: 1px solid #e3e9f2; background: #fff;
        text-decoration: none !important; color: inherit;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .help-card:hover {
        border-color: #033d2e; box-shadow: 0 8px 22px rgba(3,61,46,.08);
        transform: translateY(-1px);
    }
    .help-card-icon {
        width: 44px; height: 44px; border-radius: 10px; flex: 0 0 44px;
        display: flex; align-items: center; justify-content: center;
        background: #eef7f3; color: #033d2e; font-size: 20px;
    }
    .help-card h4 {
        margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #033d2e;
    }
    .help-card p { margin: 0; color: #5b6880; font-size: 13.5px; line-height: 1.45; }
</style>
<script>
    $("ul#side-main-menu #help-menu").addClass("active");
</script>
@endsection
