@extends('beyond.layout')

@section('title', 'Contact OGERA Agency')
@section('meta_description', 'Get in touch with OGERA Agency — business development, events, and equipment rental in Kigali.')

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::text('contact.hero_title', 'Contact <em>OGERA</em>'),
    'subtitle' => \App\Support\SiteContent::text('contact.hero_subtitle', 'Have a question, need a quotation, or want to explore a partnership? We usually respond within one business day.'),
])

@include('beyond.partials.contact_section')

@endsection
