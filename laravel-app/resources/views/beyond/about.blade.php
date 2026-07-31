@extends('beyond.layout')

@section('title', 'About OGERA Agency')
@section('meta_description', 'Learn about OGERA Agency — business development, events, and equipment rental crafted in Kigali.')

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::text('about.hero_title', 'About OGERA'),
    'subtitle' => \App\Support\SiteContent::text('about.hero_subtitle', 'We help businesses, individuals and events succeed through strategy, resources, and execution.'),
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="p-8 rounded-2xl bg-gray-50 border-t-4 border-t-brand-gold shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <i data-lucide="target" class="w-7 h-7 text-brand-gold"></i>
                    <h2 class="text-2xl font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.mission_heading', 'Our Mission') }}</h2>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    {{ \App\Support\SiteContent::text('about.mission_text', 'To provide innovative business solutions, premium rental services, and unforgettable events that empower our clients to succeed.') }}
                </p>
            </div>
            <div class="p-8 rounded-2xl bg-gray-50 border-t-4 border-t-brand-blue shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <i data-lucide="eye" class="w-7 h-7 text-brand-blue"></i>
                    <h2 class="text-2xl font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.vision_heading', 'Our Vision') }}</h2>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    {{ \App\Support\SiteContent::text('about.vision_text', 'To redefine excellence through innovative business consulting, world-class event management, and dependable rental services that exceed expectations.') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach ([['2','Years Experience'],['50+','Projects Completed'],['7','Team Members'],['20+','Global Partners']] as [$value, $label])
                <div class="text-center">
                    <div class="text-4xl font-bold text-brand-blue mb-2">{{ $value }}</div>
                    <div class="text-gray-600 font-medium">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if(isset($leaders) && $leaders->count())
<section id="leadership" class="py-20 bg-brand-blue">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-white mb-4">{{ \App\Support\SiteContent::text('about.leadership_heading', 'Our Leadership') }}</h2>
            <div class="h-1 w-24 bg-brand-gold mx-auto"></div>
            <p class="mt-4 text-xl text-gray-300">{{ \App\Support\SiteContent::text('about.leadership_subtext', 'The visionaries driving OGERA Agency forward') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($leaders as $leader)
                <div class="group flex flex-col items-center text-center">
                    <div class="relative mb-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-gold to-[#F7E7CE] rounded-full blur opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative w-48 h-48 rounded-full p-1 bg-gradient-to-br from-brand-gold to-[#8a701f]">
                            <div class="w-full h-full rounded-full overflow-hidden border-4 border-brand-blue bg-gray-200">
                                @if($leader->photoPublicUrl())
                                    <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                        <i data-lucide="user" class="w-16 h-16"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-white">
                        {{ $leader->name }}
                        @if($leader->country)
                            <span class="ml-1" title="{{ $leader->country }}">{{ $leader->countryFlag() ?: '' }}</span>
                        @endif
                    </h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-brand-gold">{{ $leader->title }}</p>
                    @if($leader->description)
                        <p class="mt-3 text-gray-300 text-sm leading-relaxed max-w-sm">{{ $leader->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-brand-blue mb-12">{{ \App\Support\SiteContent::text('about.values_heading', 'Our Core Values') }}</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach ([
                ['users', 'Client First', 'We prioritize our clients\' needs and success above all else.'],
                ['award', 'Integrity', 'We conduct business with transparency, honesty, and ethical standards.'],
                ['briefcase', 'Innovation', 'We constantly evolve and adapt to the latest technological advancements.'],
            ] as [$icon, $title, $desc])
                <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow">
                    <i data-lucide="{{ $icon }}" class="w-12 h-12 text-brand-gold mx-auto mb-4"></i>
                    <h3 class="text-xl font-bold text-brand-blue mb-2">{{ $title }}</h3>
                    <p class="text-gray-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-brand-blue mb-3">{{ \App\Support\SiteContent::text('about.registration_heading', 'Company Registration') }}</h2>
        <p class="text-gray-600 mb-8">{{ \App\Support\SiteContent::text('about.registration_text', 'OGERA Agency is a duly registered company in Rwanda.') }}</p>
        <div class="inline-flex flex-col items-center gap-4 bg-white rounded-2xl shadow-md p-6">
            <img src="{{ asset('public/branding/ogera-registration-qr.png') }}" alt="OGERA Agency registration QR code" class="w-44 h-44 object-contain">
            <div>
                <div class="text-sm uppercase tracking-wide text-gray-500">TIN Number</div>
                <div class="text-2xl font-bold text-brand-blue">{{ \App\Support\OgeraLandingContent::footer()['tin'] }}</div>
            </div>
            <p class="text-xs text-gray-500 max-w-xs">Scan the QR code to verify OGERA Agency's official company registration.</p>
        </div>
    </div>
</section>

<section class="py-16 bg-gradient-to-r from-brand-blue to-brand-dark text-white text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-6">{{ \App\Support\SiteContent::text('about.cta_heading', 'Ready to work with us?') }}</h2>
        <p class="text-xl mb-8 opacity-90">{{ \App\Support\SiteContent::text('about.cta_text', "Let's build something extraordinary together.") }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/contact') }}"
               class="inline-flex items-center justify-center gap-2 bg-brand-gold text-brand-blue font-bold text-lg px-8 py-4 rounded-full hover:bg-white hover:scale-105 transition-all">
                <i data-lucide="mail" class="w-5 h-5"></i> Contact Us
            </a>
            <a href="https://wa.me/250786887936" target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-2 border-2 border-white/70 text-white font-bold text-lg px-8 py-4 rounded-full hover:bg-white hover:text-brand-blue transition-all">
                <i data-lucide="message-circle" class="w-5 h-5"></i> Chat on WhatsApp
            </a>
        </div>
    </div>
</section>

@endsection
