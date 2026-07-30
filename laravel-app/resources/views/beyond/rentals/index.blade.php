@extends('beyond.layout')

@section('title', 'Equipment Rentals')
@section('meta_description', 'Request equipment rentals from OGERA Agency in Kigali.')

@section('content')
<div class="min-h-screen pb-20" style="background: var(--og-warm, #f8f6ef);">
    @include('beyond.partials.hero', [
        'title' => 'Equipment Rentals',
        'subtitle' => 'Submit a booking request — our team will confirm availability and follow up on WhatsApp.',
    ])

    <div class="max-w-3xl mx-auto px-4 -mt-6 relative z-10">
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-6 md:p-8">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('beyond.rentals.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-gray-700">Full Name *</label>
                        <input required name="full_name" value="{{ old('full_name') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">WhatsApp Number *</label>
                        <div class="flex gap-2 mt-1">
                            <select name="country_code" class="rounded-md border border-gray-200 px-2 py-2 w-28">
                                @foreach(['+250','+237','+256','+254','+233','+234','+1','+44'] as $code)
                                    <option value="{{ $code }}" @if(old('country_code','+250')===$code) selected @endif>{{ $code }}</option>
                                @endforeach
                            </select>
                            <input required name="phone" value="{{ old('phone') }}" class="flex-1 rounded-md border border-gray-200 px-3 py-2" placeholder="786 887 936">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Company</label>
                        <input name="company_name" value="{{ old('company_name') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Address</label>
                        <input name="address" value="{{ old('address') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Start date *</label>
                        <input required type="date" name="start_date" value="{{ old('start_date') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">End date *</label>
                        <input required type="date" name="end_date" value="{{ old('end_date') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-gray-700">Equipment needed *</label>
                        <textarea required name="equipment_needed" rows="3" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2" placeholder="e.g. 2× LED screens, PA system, cameras…">{{ old('equipment_needed') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-gray-700">Additional notes</label>
                        <textarea name="notes" rows="2" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="w-full bg-brand-gold hover:bg-[#b5952f] text-brand-blue font-bold py-3 rounded-md">
                    Submit Booking Request
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
