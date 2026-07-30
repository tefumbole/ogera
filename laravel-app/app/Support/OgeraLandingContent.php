<?php

namespace App\Support;

/**
 * Static public landing copy for OGERA.
 * Read-only helper — no DB writes. Prefer SiteContent when a key already exists.
 */
class OgeraLandingContent
{
    public static function hero()
    {
        return [
            'eyebrow' => 'OGERA Agency · Kigali',
            'title' => 'Where ambition meets <em>excellence</em>',
            'description' => 'Business development, unforgettable events, and premium equipment rental — crafted for brands and hosts who expect more.',
            'image' => asset('public/branding/ogera-hero.jpg'),
            // Set a public path later (e.g. branding/ogera-hero.mp4) to enable video.
            'video' => null,
            'ctas' => [
                ['label' => 'Grow My Business', 'url' => url('/about') . '?service=business#contact', 'style' => 'primary'],
                ['label' => 'Plan My Event', 'url' => url('/events'), 'style' => 'ghost'],
                ['label' => 'Rent Equipment', 'url' => url('/rentals'), 'style' => 'ghost'],
            ],
            'meta' => 'Mövenpick Hotel · KN 4 Avenue · Kigali',
        ];
    }

    public static function about()
    {
        return [
            'label' => 'About OGERA',
            'heading' => 'A creative agency built for <em>results</em>',
            'body' => 'OGERA Agency partners with ambitious brands, institutions, and hosts across Rwanda. We combine strategic business development, event excellence, and reliable equipment rental under one roof.',
            'mission' => [
                'title' => 'Mission',
                'body' => 'To elevate brands and experiences through strategy, creativity, and flawless execution — so every project moves the needle.',
            ],
            'vision' => [
                'title' => 'Vision',
                'body' => 'To be the agency of choice in East Africa for businesses and hosts who refuse to settle for ordinary.',
            ],
        ];
    }

    public static function services()
    {
        return [
            'label' => 'What we do',
            'heading' => 'Three pillars. One standard of <em>excellence</em>.',
            'items' => [
                [
                    'num' => '01',
                    'title' => 'Business Development',
                    'body' => 'Strategy, brand positioning, partnerships, and growth programs that turn ambition into measurable traction.',
                    'cta' => 'Start a conversation',
                    'url' => url('/about') . '?service=business#contact',
                ],
                [
                    'num' => '02',
                    'title' => 'Event Management',
                    'body' => 'Corporate, social, and cultural events designed end-to-end — from concept and production to guest experience.',
                    'cta' => 'Plan an event',
                    'url' => url('/events'),
                ],
                [
                    'num' => '03',
                    'title' => 'Equipment Rental',
                    'body' => 'Premium AV, staging, and event equipment — delivered, installed, and supported so your production runs smoothly.',
                    'cta' => 'Browse rentals',
                    'url' => url('/rentals'),
                ],
            ],
        ];
    }

    public static function why()
    {
        return [
            'label' => 'Why OGERA',
            'heading' => 'Clarity, craft, and care at every step',
            'body' => 'We are selective about the work we take on so we can stay hands-on, accountable, and relentlessly detail-oriented.',
            'strengths' => [
                ['title' => 'Strategic first', 'body' => 'Every brief starts with outcomes — not decoration.'],
                ['title' => 'End-to-end ownership', 'body' => 'One team from concept through delivery and wrap.'],
                ['title' => 'Local fluency', 'body' => 'Kigali-rooted networks with international standards.'],
                ['title' => 'Production-ready', 'body' => 'In-house rental capability means fewer gaps on show day.'],
            ],
        ];
    }

    /**
     * Statistics — only items with non-null values should be rendered.
     * Fill approved numbers here when the client provides them.
     */
    public static function statistics()
    {
        return [
            // ['value' => '120+', 'label' => 'Events delivered'],
            // ['value' => '40+', 'label' => 'Brand partners'],
            // ['value' => '8', 'label' => 'Years of craft'],
            // ['value' => '1', 'label' => 'Standard: excellence'],
        ];
    }

    public static function framework()
    {
        return [
            'label' => 'How we work',
            'heading' => 'A clear path from brief to <em>applause</em>',
            'steps' => [
                ['num' => '01', 'title' => 'Discover', 'body' => 'We listen closely — goals, audience, constraints, and what success looks like.'],
                ['num' => '02', 'title' => 'Design', 'body' => 'We shape a strategy and creative plan you can approve with confidence.'],
                ['num' => '03', 'title' => 'Deliver', 'body' => 'Our team executes with precision — vendors, tech, and timing under one lead.'],
                ['num' => '04', 'title' => 'Delight', 'body' => 'We close the loop with measurement, learning, and a partnership ready for what’s next.'],
            ],
        ];
    }

    public static function cta()
    {
        return [
            'heading' => 'Ready to build something memorable?',
            'body' => 'Tell us about your brand, event, or rental need. We’ll respond with a clear next step.',
            'primary' => ['label' => 'Start a Project', 'url' => url('/about') . '#contact'],
            'secondary' => ['label' => 'View Gallery', 'url' => url('/gallery')],
        ];
    }

    public static function contactDefaults()
    {
        return [
            'heading' => 'Get in Touch',
            'intro' => 'Have a question, need a quotation, or want to explore a partnership? Reach out to the OGERA team.',
            'office_name' => 'OGERA Agency',
            'office_line1' => 'Mövenpick Hotel, KN 4 Avenue',
            'office_line2' => 'Kigali, Rwanda',
            'person_name' => 'OGERA Concierge',
            'person_role' => 'Client Relations',
            'phone' => '+250 786 887 936',
            'email' => 'info@ogeragency.com',
            'website' => 'www.ogeragency.com',
            'hours_weekday' => '9:00 AM - 6:00 PM',
            'hours_weekend' => 'By appointment',
        ];
    }

    public static function footer()
    {
        return [
            'blurb' => 'Business development, events, and equipment rental — crafted in Kigali for brands and hosts who expect more.',
            'address' => 'Mövenpick Hotel, KN 4 Avenue, Kigali',
            'email' => 'info@ogeragency.com',
            'phone' => '+250 786 887 936',
            'phone_tel' => '+250786887936',
            'website' => 'www.ogeragency.com',
            'website_url' => 'https://www.ogeragency.com',
            'wa' => '250786887936',
        ];
    }

    /** Service query values → subject line for contact form preselect */
    public static function serviceSubjects()
    {
        return [
            'business' => 'Business Development Inquiry',
            'events' => 'Event Planning Inquiry',
            'rentals' => 'Equipment Rental Inquiry',
            'partner' => 'Partnership Inquiry',
            'vendor' => 'Vendor / Supplier Inquiry',
            'quotation' => 'Request for Quotation',
        ];
    }
}
