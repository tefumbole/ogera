@php
    $defaults = \App\Support\OgeraLandingContent::contactDefaults();
    $subjects = \App\Support\OgeraLandingContent::serviceSubjects();
    $waPhone = preg_replace('/\D+/', '', \App\Support\SiteContent::text('contact.phone', $defaults['phone']));
    $preselectService = request()->query('service', '');
    $preselectSubject = $subjects[$preselectService] ?? '';
@endphp

<section id="contact" class="py-16 scroll-mt-24" style="background: var(--og-warm, #f8f6ef);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-10 sm:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-blue mb-4 ogera-serif" style="font-weight:400;">
                {{ \App\Support\SiteContent::text('contact.heading', $defaults['heading']) }}
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ \App\Support\SiteContent::text('contact.intro', $defaults['intro']) }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl border-t-4 border-t-brand-blue shadow-md hover:shadow-lg transition-all p-6">
                    <h3 class="text-xl font-bold text-brand-blue mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5"></i> Office Location
                    </h3>
                    <div class="space-y-3 text-gray-600">
                        <p class="font-semibold text-gray-800">{{ \App\Support\SiteContent::text('contact.office_name', $defaults['office_name']) }}</p>
                        @php
                            $officeLine1 = \App\Support\SiteContent::text('contact.office_line1', $defaults['office_line1']);
                            $officeLine2 = \App\Support\SiteContent::text('contact.office_line2', $defaults['office_line2']);
                            $mapsQuery = trim($officeLine1 . ', ' . $officeLine2);
                            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapsQuery);
                        @endphp
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="block hover:text-brand-blue transition-colors" title="Open in Google Maps">
                            <p>{{ $officeLine1 }}</p>
                            <p>{{ $officeLine2 }}</p>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl border-t-4 border-t-brand-gold shadow-md hover:shadow-lg transition-all p-6">
                    <h3 class="text-xl font-bold text-brand-blue mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-gold"></i> Contact
                    </h3>
                    <div class="space-y-4 text-gray-600">
                        <div>
                            <p class="font-bold text-gray-800">{{ \App\Support\SiteContent::text('contact.person_name', $defaults['person_name']) }}</p>
                            <p class="text-sm text-gray-500">{{ \App\Support\SiteContent::text('contact.person_role', $defaults['person_role']) }}</p>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-blue-100 p-2 rounded-full text-brand-blue"><i data-lucide="phone" class="w-4 h-4"></i></div>
                            <div>
                                <p class="font-medium">{{ \App\Support\SiteContent::text('contact.phone', $defaults['phone']) }}</p>
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="text-brand-gold hover:text-brand-blue text-xs font-semibold inline-flex items-center gap-1">
                                    <i data-lucide="message-circle" class="w-3 h-3"></i> Chat on WhatsApp
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-yellow-100 p-2 rounded-full text-brand-gold"><i data-lucide="mail" class="w-4 h-4"></i></div>
                            <a href="mailto:{{ \App\Support\SiteContent::text('contact.email', $defaults['email']) }}" class="font-medium hover:text-brand-blue">{{ \App\Support\SiteContent::text('contact.email', $defaults['email']) }}</a>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-gray-200 p-2 rounded-full text-gray-700"><i data-lucide="globe" class="w-4 h-4"></i></div>
                            <a href="https://www.ogeragency.com" class="font-medium hover:text-brand-blue">{{ \App\Support\SiteContent::text('contact.website', $defaults['website']) }}</a>
                        </div>
                    </div>
                </div>

                <div class="bg-brand-blue text-white shadow-md rounded-xl overflow-hidden relative p-6">
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="bg-white/20 p-3 rounded-full shrink-0"><i data-lucide="clock" class="w-6 h-6"></i></div>
                        <div>
                            <h3 class="font-bold text-lg mb-2 text-brand-gold">Business Hours</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-blue-100">Mon - Fri:</span><span class="font-medium">{{ \App\Support\SiteContent::text('contact.hours_weekday', $defaults['hours_weekday']) }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-blue-100">Sat & Sun:</span><span class="font-medium opacity-80">{{ \App\Support\SiteContent::text('contact.hours_weekend', $defaults['hours_weekend']) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="shadow-xl border-0 h-full rounded-xl overflow-hidden bg-white">
                    <div class="bg-gradient-to-r from-brand-dark to-brand-blue p-6 text-white">
                        <h3 class="text-2xl font-bold flex items-center gap-2">
                            <i data-lucide="send" class="w-6 h-6 text-brand-gold"></i> Send us a Direct Message
                        </h3>
                        <p class="text-blue-100 mt-1">Fill out the form below and we'll instantly receive it via WhatsApp.</p>
                    </div>
                    <div class="p-5 sm:p-8 md:p-10">
                        <form id="contact-form" class="space-y-6" onsubmit="return submitContact(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Full Name <span class="text-red-500">*</span></label>
                                    <input required name="name" type="text" placeholder="e.g. John Doe"
                                           class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Email Address <span class="text-gray-400 font-normal">(optional)</span></label>
                                    <input name="email" type="email" placeholder="e.g. john@example.com"
                                           class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Service interest</label>
                                <select name="service" id="contact-service"
                                        class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                                    <option value="">General inquiry</option>
                                    @foreach ($subjects as $key => $label)
                                        <option value="{{ $key }}" @if($preselectService === $key) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Subject <span class="text-red-500">*</span></label>
                                <input required name="subject" id="contact-subject" type="text"
                                       value="{{ $preselectSubject }}"
                                       placeholder="What is this regarding?"
                                       class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Your Message <span class="text-red-500">*</span></label>
                                <textarea required name="message" rows="6" placeholder="Please provide details about your inquiry..."
                                          class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 resize-none focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none"></textarea>
                            </div>
                            <div id="contact-success" class="hidden rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"></div>
                            <div class="pt-4 flex flex-col sm:flex-row gap-4">
                                <button type="submit"
                                        class="w-full sm:w-auto px-8 bg-brand-blue hover:bg-[#052e24] text-white font-bold h-12 text-lg rounded-md shadow-md inline-flex items-center justify-center gap-2">
                                    <i data-lucide="send" class="w-5 h-5"></i> Send Message
                                </button>
                                <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Hello OGERA Agency, I would like to inquire about...') }}"
                                   target="_blank" rel="noopener"
                                   class="w-full sm:w-auto px-8 border-2 border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-bold h-12 text-lg rounded-md inline-flex items-center justify-center gap-2 transition-colors">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i> Open WhatsApp
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    var subjects = @json($subjects);
    var serviceEl = document.getElementById('contact-service');
    var subjectEl = document.getElementById('contact-subject');

    function applyServiceFromQuery() {
        try {
            var params = new URLSearchParams(window.location.search);
            var key = params.get('service');
            if (!key && window.location.hash.indexOf('service=') !== -1) {
                // Support legacy "#contact?service=..." style links
                var hashQ = window.location.hash.split('?')[1] || '';
                key = new URLSearchParams(hashQ).get('service');
            }
            if (key && subjects[key] && serviceEl) {
                serviceEl.value = key;
                if (subjectEl && (!subjectEl.value || Object.values(subjects).indexOf(subjectEl.value) !== -1)) {
                    subjectEl.value = subjects[key];
                }
            }
        } catch (e) {}
    }

    if (serviceEl && subjectEl) {
        serviceEl.addEventListener('change', function () {
            var key = serviceEl.value;
            if (key && subjects[key]) {
                subjectEl.value = subjects[key];
            }
        });
    }

    applyServiceFromQuery();
    document.addEventListener('DOMContentLoaded', applyServiceFromQuery);
})();

function submitContact(e) {
    e.preventDefault();
    var f = e.target;
    var name = f.name.value.trim();
    var email = f.email.value.trim();
    var subject = f.subject.value.trim();
    var message = f.message.value.trim();
    var service = f.service ? f.service.value : '';
    var serviceLine = service ? ('\n*Service:* ' + (f.service.options[f.service.selectedIndex].text || service)) : '';
    var emailLine = email ? ('\n*Email:* ' + email) : '';
    var text = '*New Contact Form Submission*\n\n*Name:* ' + name + emailLine + '\n*Subject:* ' + subject + serviceLine + '\n\n*Message:*\n' + message;
    window.open('https://wa.me/{{ $waPhone }}?text=' + encodeURIComponent(text), '_blank');
    var el = document.getElementById('contact-success');
    el.textContent = 'Thank you! Your message was opened in WhatsApp. We will get back to you shortly.';
    el.classList.remove('hidden');
    f.reset();
    return false;
}
</script>
@endpush
