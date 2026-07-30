@extends('beyond.layout')

@section('title', 'Business · Events · Rentals')
@section('meta_description', 'OGERA Agency — business development, event management, and equipment rental in Kigali, Rwanda.')

@section('content')

@php
    $hero = \App\Support\OgeraLandingContent::hero();

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
</section>

@endsection
