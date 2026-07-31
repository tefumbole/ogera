@extends('beyond.layout')

@section('title', 'Our Services')
@section('meta_description', 'OGERA Agency services — business development, event management, and equipment rental in Kigali, Rwanda.')

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::html('services.hero_title', 'Our <em>Services</em>'),
    'subtitle' => \App\Support\SiteContent::text('services.hero_subtitle', 'Business growth, events, and equipment — delivered to one standard of excellence.'),
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            @foreach ($services as $i => $service)
                @php
                    $accents = [
                        ['border' => 'border-t-brand-gold', 'icon' => 'text-brand-gold'],
                        ['border' => 'border-t-brand-blue', 'icon' => 'text-brand-blue'],
                        ['border' => 'border-t-brand-gold', 'icon' => 'text-brand-gold'],
                    ];
                    $accent = $accents[$i % 3];
                @endphp
                <div class="p-8 rounded-2xl bg-gray-50 {{ $accent['border'] }} border-t-4 shadow-sm hover:shadow-md transition-shadow flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <i data-lucide="{{ $service['icon'] }}" class="w-7 h-7 {{ $accent['icon'] }} shrink-0"></i>
                        <h2 class="text-2xl font-bold text-brand-blue leading-snug">{{ $service['title'] }}</h2>
                    </div>
                    <p class="text-gray-600 leading-relaxed flex-grow mb-6">{{ $service['description'] }}</p>
                    <a href="{{ $service['url'] ?? url('/contact') }}"
                       class="inline-flex items-center gap-2 text-brand-blue font-semibold hover:text-brand-gold transition-colors self-start">
                        Learn More <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
