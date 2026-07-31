@extends('beyond.layout')

@section('title', 'Business · Events · Rentals')
@section('meta_description', 'OGERA Agency — business development, event management, and equipment rental in Kigali, Rwanda.')

@section('content')

@php
    $hero = \App\Support\OgeraLandingContent::hero();

    $heroEyebrow = \App\Support\SiteContent::text('home.hero_eyebrow', $hero['eyebrow']);
    $heroTitle = \App\Support\SiteContent::html('home.hero_title', $hero['title']);
    $heroDesc = \App\Support\SiteContent::text('home.hero_subtitle', $hero['description']);
    $ctaPrimary = \App\Support\SiteContent::text('home.cta_primary', $hero['ctas'][0]['label']);
    $ctaSecondary = \App\Support\SiteContent::text('home.cta_secondary', $hero['ctas'][1]['label']);
    $ctaLink = \App\Support\SiteContent::text('home.cta_link', $hero['link']['label']);

    $heroImageUrl = \App\Support\SiteContent::image('home.hero_image', $hero['image']);
    $heroImageUrl .= (strpos($heroImageUrl, '?') === false ? '?' : '&') . 'v=' . rawurlencode(\App\Support\AppVersion::label());
    $heroVideo = $hero['video'];
@endphp

{{-- Hero (full landing) --}}
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
        <p class="ogera-hero__eyebrow ogera-reveal">{{ $heroEyebrow }}</p>
        <h1 class="ogera-hero__title ogera-reveal">{!! $heroTitle !!}</h1>
        <p class="ogera-hero__desc ogera-reveal">{{ $heroDesc }}</p>
        <div class="ogera-hero__actions ogera-reveal">
            <a href="{{ $hero['ctas'][0]['url'] }}" class="ogera-btn ogera-btn--primary">
                {{ $ctaPrimary }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <a href="{{ $hero['ctas'][1]['url'] }}" class="ogera-btn ogera-btn--outline">
                {{ $ctaSecondary }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <a href="{{ $hero['link']['url'] }}" class="ogera-hero__link ogera-reveal">
            {{ $ctaLink }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

@endsection
