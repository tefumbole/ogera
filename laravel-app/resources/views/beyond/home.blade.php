@extends('beyond.layout')

@section('title', 'Business · Events · Rentals')
@section('meta_description', 'OGERA Agency — business development, event management, and equipment rental in Kigali, Rwanda.')

@section('content')

@php
    $hero = \App\Support\OgeraLandingContent::hero();
    $about = \App\Support\OgeraLandingContent::about();
    $services = \App\Support\OgeraLandingContent::services();
    $why = \App\Support\OgeraLandingContent::why();
    $stats = array_values(array_filter(\App\Support\OgeraLandingContent::statistics(), function ($s) {
        return !empty($s['value']);
    }));
    $framework = \App\Support\OgeraLandingContent::framework();
    $cta = \App\Support\OgeraLandingContent::cta();

    $heroImageUrl = \App\Support\SiteContent::image('home.hero_image', $hero['image']);
    $heroImageUrl .= (strpos($heroImageUrl, '?') === false ? '?' : '&') . 'v=' . rawurlencode(\App\Support\AppVersion::label());
    $heroVideo = $hero['video'];

    $galleryItems = collect();
    try {
        $galleryItems = \App\GalleryItem::published()->ordered()->take(4)->get();
    } catch (\Throwable $e) {
        $galleryItems = collect();
    }
@endphp

{{-- Hero --}}
<section class="ogera-hero" id="top">
    <div class="ogera-hero__media" aria-hidden="true">
        @if ($heroVideo)
            <video class="ogera-hero__video" autoplay muted loop playsinline poster="{{ $heroImageUrl }}">
                <source src="{{ $heroVideo }}" type="video/mp4">
            </video>
        @else
            <img
                src="{{ $heroImageUrl }}"
                alt=""
                width="1920"
                height="960"
                fetchpriority="high"
                decoding="async"
                class="ogera-hero__photo"
            >
        @endif
        <div class="ogera-hero__scrim"></div>
    </div>

    <div class="ogera-hero__inner">
        <p class="ogera-hero__eyebrow ogera-reveal">{{ $hero['eyebrow'] }}</p>
        <h1 class="ogera-hero__title ogera-reveal">{!! $hero['title'] !!}</h1>
        <p class="ogera-hero__desc ogera-reveal">{{ $hero['description'] }}</p>
        <div class="ogera-hero__actions ogera-reveal">
            @foreach ($hero['ctas'] as $btn)
                <a href="{{ $btn['url'] }}" class="ogera-btn ogera-btn--{{ $btn['style'] }}">
                    {{ $btn['label'] }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            @endforeach
        </div>
        @if (!empty($hero['link']))
            <a href="{{ $hero['link']['url'] }}" class="ogera-hero__link ogera-reveal">
                {{ $hero['link']['label'] }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        @endif
    </div>

    <a href="#about" class="ogera-scroll" aria-label="Scroll to about">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
    </a>
</section>

{{-- About + Mission / Vision --}}
<section class="ogera-section" id="about" style="background: var(--og-warm); scroll-margin-top: 5rem;">
    <div class="ogera-container">
        <p class="ogera-label">{{ $about['label'] }}</p>
        <h2 class="ogera-heading">{!! $about['heading'] !!}</h2>
        <p class="ogera-lede">{{ $about['body'] }}</p>

        <div class="ogera-cards ogera-cards--2">
            <div class="ogera-card ogera-card--mission">
                <div class="ogera-card__title">{{ $about['mission']['title'] }}</div>
                <p class="ogera-card__body">{{ $about['mission']['body'] }}</p>
            </div>
            <div class="ogera-card ogera-card--vision">
                <div class="ogera-card__title">{{ $about['vision']['title'] }}</div>
                <p class="ogera-card__body">{{ $about['vision']['body'] }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Services --}}
<section class="ogera-section" id="services" style="scroll-margin-top: 5rem;">
    <div class="ogera-container">
        <p class="ogera-label">{{ $services['label'] }}</p>
        <h2 class="ogera-heading">{!! $services['heading'] !!}</h2>

        <div class="ogera-cards ogera-cards--3">
            @foreach ($services['items'] as $item)
                <div class="ogera-card ogera-card--service">
                    <div class="ogera-card__num">{{ $item['num'] }}</div>
                    <h3 class="ogera-card__title">{{ $item['title'] }}</h3>
                    <p class="ogera-card__body">{{ $item['body'] }}</p>
                    <a href="{{ $item['url'] }}" class="ogera-card__link">
                        {{ $item['cta'] }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ url('/services') }}" class="ogera-btn ogera-btn--dark">Explore all services</a>
        </div>
    </div>
</section>

{{-- Why OGERA --}}
<section class="ogera-section" id="why" style="background: var(--og-warm); scroll-margin-top: 5rem;">
    <div class="ogera-container">
        <div class="ogera-split">
            <div>
                <p class="ogera-label">{{ $why['label'] }}</p>
                <h2 class="ogera-heading">{{ $why['heading'] }}</h2>
                <p class="ogera-lede">{{ $why['body'] }}</p>
            </div>
            <div class="ogera-strength">
                @foreach ($why['strengths'] as $s)
                    <div class="ogera-strength__item">
                        <strong>{{ $s['title'] }}</strong>
                        <p>{{ $s['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if (count($stats))
            <div class="ogera-stats">
                @foreach ($stats as $stat)
                    <div class="ogera-stat">
                        <div class="ogera-stat__value">{{ $stat['value'] }}</div>
                        <div class="ogera-stat__label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- How We Work --}}
<section class="ogera-section" id="how" style="scroll-margin-top: 5rem;">
    <div class="ogera-container">
        <p class="ogera-label">{{ $framework['label'] }}</p>
        <h2 class="ogera-heading">{!! $framework['heading'] !!}</h2>

        <div class="ogera-timeline">
            @foreach ($framework['steps'] as $step)
                <div class="ogera-step">
                    <div class="ogera-step__n">{{ $step['num'] }}</div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Gallery strip --}}
<section class="ogera-section" id="gallery" style="background: var(--og-warm); scroll-margin-top: 5rem;">
    <div class="ogera-container">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="ogera-label">Gallery</p>
                <h2 class="ogera-heading">Moments from our work</h2>
            </div>
            <a href="{{ url('/gallery') }}" class="ogera-btn ogera-btn--dark">View all</a>
        </div>

        @if ($galleryItems->isNotEmpty())
            <div class="ogera-gallery-grid">
                @foreach ($galleryItems as $item)
                    @php $src = $item->fileUrl() ?: $item->media_url; @endphp
                    @if ($src)
                        <a href="{{ url('/gallery') }}">
                            <img src="{{ $src }}" alt="{{ $item->title ?: 'Gallery' }}" loading="lazy">
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <p class="ogera-lede" style="margin-top: 1.5rem;">Gallery highlights will appear here once published from Site Content.</p>
        @endif
    </div>
</section>

{{-- Upcoming events (controller-provided) --}}
@if(!empty($homeEvents) && $homeEvents->isNotEmpty())
<section class="ogera-section">
    <div class="ogera-container">
        <p class="ogera-label">Events</p>
        <h2 class="ogera-heading">Upcoming highlights</h2>
        <div class="ogera-cards ogera-cards--3" style="margin-top: 2.5rem;">
            @foreach($homeEvents as $ev)
                <a href="{{ url('/events/' . $ev['slug']) }}" class="ogera-card ogera-card--service" style="text-decoration:none; overflow:hidden; padding:0;">
                    <div style="aspect-ratio:16/10; background:var(--og-grey); overflow:hidden;">
                        @if(!empty($ev['flyer']))
                            <img src="{{ $ev['flyer'] }}" alt="{{ $ev['title'] }}" style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>
                    <div style="padding:1.25rem 1.4rem 1.5rem;">
                        <h3 class="ogera-card__title" style="font-size:1.2rem;">{{ $ev['title'] }}</h3>
                        @if(!empty($ev['start']))
                            <p class="ogera-card__body" style="margin:0;">{{ $ev['start']->format('D, M j, Y g:i A') }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a href="{{ url('/events') }}" class="ogera-btn ogera-btn--dark">View all events</a>
        </div>
    </div>
</section>
@endif

{{-- Testimonials — empty until real content exists --}}
<section class="ogera-section" id="testimonials" style="scroll-margin-top: 5rem;">
    <div class="ogera-container" style="text-align:center;">
        <p class="ogera-label">Testimonials</p>
        <h2 class="ogera-heading" style="margin-left:auto;margin-right:auto;">Client voices</h2>
        <p class="ogera-lede" style="margin-left:auto;margin-right:auto;">
            Client stories will appear here once approved. Until then, we’d love to hear about your project.
        </p>
        <div style="margin-top:1.75rem;">
            <a href="{{ url('/about') }}#contact" class="ogera-btn ogera-btn--primary">Start a Project</a>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="ogera-section" style="padding-top:0;">
    <div class="ogera-container">
        <div class="ogera-cta-band">
            <h2>{{ $cta['heading'] }}</h2>
            <p>{{ $cta['body'] }}</p>
            <div class="ogera-cta-band__actions">
                <a href="{{ $cta['primary']['url'] }}" class="ogera-btn ogera-btn--primary">{{ $cta['primary']['label'] }}</a>
                <a href="{{ $cta['secondary']['url'] }}" class="ogera-btn ogera-btn--ghost">{{ $cta['secondary']['label'] }}</a>
            </div>
        </div>
    </div>
</section>

{{-- Contact on home too for #contact deep links from nav when on home --}}
@include('beyond.partials.contact_section')

@endsection
