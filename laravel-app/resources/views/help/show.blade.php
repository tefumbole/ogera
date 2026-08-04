@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
            <div>
                <a href="{{ route('help.index') }}" class="text-muted" style="font-size:13px;">
                    <i class="dripicons-arrow-thin-left"></i> All chapters
                </a>
                <h3 class="mb-1 mt-1" style="color:#033d2e;font-weight:800;">
                    <i class="{{ $chapter['icon'] }}"></i> {{ $chapter['title'] }}
                </h3>
                <p class="text-muted mb-0">
                    {{ $chapter['summary'] }}
                    @if($updatedAt) &middot; updated {{ $updatedAt }}@endif
                    &middot; {{ $version }}
                </p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="dripicons-print"></i> Print
            </button>
        </div>

        <div class="row">
            <div class="col-lg-3 d-none d-lg-block mb-3">
                <div class="card help-toc">
                    <div class="card-body">
                        <div class="help-toc-title">Chapters</div>
                        <ul class="list-unstyled mb-0">
                            @foreach($chapters as $s => $c)
                                <li>
                                    <a href="{{ route('help.show', $s) }}" class="{{ $s === $slug ? 'active' : '' }}">
                                        {{ $c['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                @if($html === null)
                    <div class="alert alert-warning">
                        This chapter is not available in this deployment
                        (<code>{{ \App\Http\Controllers\UserHelpController::DIR }}/{{ $slug }}.md</code>).
                    </div>
                @else
                    <div class="card">
                        <div class="card-body help-doc" id="help-doc">
                            {!! $html !!}
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-between flex-wrap mt-3 mb-4" style="gap:10px;">
                    @if($prevSlug)
                        <a class="btn btn-outline-secondary" href="{{ route('help.show', $prevSlug) }}">
                            <i class="dripicons-arrow-thin-left"></i> {{ $chapters[$prevSlug]['title'] }}
                        </a>
                    @else
                        <span></span>
                    @endif
                    @if($nextSlug)
                        <a class="btn btn-primary" href="{{ route('help.show', $nextSlug) }}">
                            {{ $chapters[$nextSlug]['title'] }} <i class="dripicons-arrow-thin-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<style>
    .help-toc { position: sticky; top: 16px; }
    .help-toc-title {
        font-size: 12px; font-weight: 800; letter-spacing: .04em;
        text-transform: uppercase; color: #7a8799; margin-bottom: 10px;
    }
    .help-toc a {
        display: block; padding: 7px 10px; border-radius: 8px;
        color: #334155; text-decoration: none; font-size: 14px; margin-bottom: 2px;
    }
    .help-toc a:hover { background: #f3f7f5; color: #033d2e; }
    .help-toc a.active { background: #e8f4ef; color: #033d2e; font-weight: 700; }

    .help-doc { max-width: 860px; line-height: 1.7; color: #23324d; }
    .help-doc h1 { font-size: 26px; font-weight: 800; color: #033d2e; margin: 0 0 10px; }
    .help-doc h2 {
        font-size: 20px; font-weight: 800; color: #033d2e;
        margin: 32px 0 12px; padding-top: 14px; border-top: 2px solid #eef2f7;
    }
    .help-doc h3 { font-size: 16px; font-weight: 700; color: #1f2a44; margin: 22px 0 8px; }
    .help-doc p { margin: 0 0 12px; }
    .help-doc ul, .help-doc ol { margin: 0 0 14px; padding-left: 22px; }
    .help-doc li { margin-bottom: 6px; }
    .help-doc code {
        background: #f3f6fb; color: #b0357a; padding: 2px 5px;
        border-radius: 4px; font-size: 13px;
    }
    .help-doc img {
        display: block; max-width: 100%; height: auto;
        border: 1px solid #e3e9f2; border-radius: 10px;
        margin: 8px 0 18px; box-shadow: 0 6px 18px rgba(15,23,42,.06);
    }
    .help-doc table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
    .help-doc th, .help-doc td { border: 1px solid #e3e9f2; padding: 8px 10px; font-size: 14px; text-align: left; }
    .help-doc th { background: #f6f9fc; color: #033d2e; font-weight: 700; }
    .help-doc blockquote {
        border-left: 4px solid #c6ab47; margin: 0 0 14px;
        padding: 6px 0 6px 14px; color: #5b6880; background: #fbfaf5;
    }
    .help-doc hr { border: 0; border-top: 2px solid #eef2f7; margin: 28px 0; }
    .help-doc strong { color: #1f2a44; }

    @media print {
        .side-navbar, .navbar, .footer, #beyond-module-tabs,
        .help-toc, .btn, a[href="{{ route('help.index') }}"] { display: none !important; }
        .page-content, .content-inner { margin: 0 !important; padding: 0 !important; }
        .help-doc { max-width: none; }
        .card { border: 0 !important; box-shadow: none !important; }
        .help-doc img { break-inside: avoid; box-shadow: none; }
    }
</style>
<script>
    $("ul#side-main-menu #help-menu").addClass("active");
</script>
@endsection
