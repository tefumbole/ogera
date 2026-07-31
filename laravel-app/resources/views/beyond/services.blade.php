@extends('beyond.layout')

@section('title', 'Our Services')
@section('meta_description', 'OGERA Agency services — business development, event management, and equipment rental in Kigali, Rwanda.')

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::html('services.hero_title', 'Our <em>Services</em>'),
    'subtitle' => \App\Support\SiteContent::text('services.hero_subtitle', 'Business growth, events, and equipment — delivered to one standard of excellence.'),
])

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-brand-blue">{{ \App\Support\SiteContent::text('services.heading', 'Explore Our Expertise') }}</h2>
            <p class="text-xl text-gray-600 mt-4">{{ \App\Support\SiteContent::text('services.subheading', "Business growth, unforgettable events, and reliable equipment — all under one roof.") }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @foreach ($services as $service)
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col">
                    <div class="text-6xl mb-4 flex-shrink-0">{{ $service['emoji'] }}</div>
                    <h3 class="text-2xl font-bold text-brand-blue mb-3">{{ $service['title'] }}</h3>
                    <p class="text-gray-700 text-sm flex-grow mb-4">{{ $service['description'] }}</p>
                    <a href="{{ $service['url'] ?? url('/contact') }}"
                       class="inline-flex items-center gap-2 bg-brand-blue hover:bg-brand-dark text-white px-6 py-3 rounded-md text-sm font-semibold self-start">
                        Learn More <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-gradient-to-r from-brand-blue to-brand-light">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Get Started?</h2>
        <p class="text-xl text-gray-200 mb-8">Contact us today to discuss your project requirements and receive a customized quote.</p>
        <a href="https://wa.me/250786887936" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 bg-brand-gold text-brand-blue px-8 py-4 text-lg font-bold rounded-lg shadow-xl hover:shadow-2xl">
            <i data-lucide="message-circle" class="w-5 h-5"></i> Chat on WhatsApp
        </a>
    </div>
</section>

@endsection
