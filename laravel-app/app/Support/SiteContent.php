<?php

namespace App\Support;

use App\SiteSetting;

/**
 * Editable front-end content. Values are stored in site_settings under the
 * "content." prefix. The schema() drives the admin editor and the defaults
 * keep the public site unchanged until an admin overrides a field.
 */
class SiteContent
{
    /** Raw stored value for a content key, or the given default. */
    public static function get($key, $default = '')
    {
        $val = SiteSetting::getValue('content.' . $key, null);

        return ($val === null || $val === '') ? $default : $val;
    }

    public static function text($key, $default = '')
    {
        return self::get($key, $default);
    }

    public static function html($key, $default = '')
    {
        return self::get($key, $default);
    }

    /** Resolve an image field to a usable URL, falling back to the default. */
    public static function image($key, $default = '')
    {
        $val = SiteSetting::getValue('content.' . $key, null);
        if (! $val) {
            return $default;
        }
        // Absolute URLs / root-relative paths are returned as-is.
        if (preg_match('#^(https?:)?//#', $val) || strpos($val, '/') === 0) {
            return $val;
        }

        return url('public/' . ltrim($val, '/'));
    }

    /** Persist a scalar content value. */
    public static function put($key, $value)
    {
        SiteSetting::setValue('content.' . $key, $value);
    }

    /**
     * Editable page schema. Each page: label, url, and fields keyed by name.
     * Field: [type, label, default]. type in {text, textarea, html, image}.
     * Defaults match the live OGERA landing (OgeraLandingContent).
     */
    public static function schema()
    {
        $hero = OgeraLandingContent::hero();
        $contact = OgeraLandingContent::contactDefaults();
        $cta = OgeraLandingContent::cta();

        return [
            'home' => [
                'label' => 'Home',
                'url' => '/',
                'fields' => [
                    'hero_eyebrow'          => ['text', 'Hero eyebrow', $hero['eyebrow']],
                    'hero_title'            => ['html', 'Hero title (HTML allowed)', $hero['title']],
                    'hero_subtitle'         => ['textarea', 'Hero subtitle', $hero['description']],
                    'hero_image'            => ['image', 'Hero background image', '/public/branding/ogera-hero.jpg'],
                    'cta_primary'           => ['text', 'Hero primary button text', $hero['ctas'][0]['label']],
                    'cta_secondary'         => ['text', 'Hero secondary button text', $hero['ctas'][1]['label']],
                    'cta_link'              => ['text', 'Hero text link', $hero['link']['label']],
                    'services_heading'      => ['text', 'Services heading', 'Our Services'],
                    'services_subheading'   => ['text', 'Services subheading', 'Business growth, unforgettable events, and reliable equipment — all under one roof.'],
                    'why_heading'           => ['text', 'Why-us heading', 'Why OGERA'],
                    'why_subheading'        => ['text', 'Why-us subheading', 'Clarity, craft, and care at every step'],
                    'cta_heading'           => ['text', 'Bottom CTA heading', $cta['heading']],
                    'cta_text'              => ['textarea', 'Bottom CTA text', $cta['body']],
                ],
            ],
            'about' => [
                'label' => 'About',
                'url' => '/about',
                'fields' => [
                    'hero_title'         => ['text', 'Hero title', 'About OGERA'],
                    'hero_subtitle'      => ['textarea', 'Hero subtitle', 'We help businesses, individuals and events succeed through strategy, resources, and execution.'],
                    'mission_heading'    => ['text', 'Mission heading', 'Our Mission'],
                    'mission_text'       => ['textarea', 'Mission text', 'To provide innovative business solutions, premium rental services, and unforgettable events that empower our clients to succeed.'],
                    'vision_heading'     => ['text', 'Vision heading', 'Our Vision'],
                    'vision_text'        => ['textarea', 'Vision text', 'To redefine excellence through innovative business consulting, world-class event management, and dependable rental services that exceed expectations.'],
                    'leadership_heading' => ['text', 'Leadership heading', 'Our Leaders'],
                    'leadership_subtext' => ['text', 'Leadership subtext', 'The visionaries driving OGERA Agency forward'],
                    'values_heading'     => ['text', 'Core values heading', 'Our Core Values'],
                    'registration_heading' => ['text', 'Registration heading', 'Company Registration'],
                    'registration_text'  => ['text', 'Registration text', 'OGERA Agency is a duly registered company in Rwanda.'],
                    'cta_heading'        => ['text', 'CTA heading', 'Ready to work with us?'],
                    'cta_text'           => ['text', 'CTA text', "Let's build something extraordinary together."],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'url' => '/services',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <em>Services</em>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Business growth, events, and equipment — delivered to one standard of excellence.'],
                    'heading'       => ['text', 'Section heading', 'Explore Our Expertise'],
                    'subheading'    => ['textarea', 'Section subheading', 'Business growth, unforgettable events, and reliable equipment — all under one roof.'],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'url' => '/projects',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <em>Projects</em>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Strategy, events, and rentals delivered with clarity and precision'],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'url' => '/contact',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Contact <em>OGERA</em>'],
                    'hero_subtitle' => ['textarea', 'Hero subtitle', 'Have a question, need a quotation, or want to explore a partnership? We usually respond within one business day.'],
                    'heading'       => ['text', 'Page heading', $contact['heading']],
                    'intro'         => ['textarea', 'Intro text', $contact['intro']],
                    'office_name'   => ['text', 'Office name', $contact['office_name']],
                    'office_line1'  => ['text', 'Office address line 1', $contact['office_line1']],
                    'office_line2'  => ['text', 'Office address line 2', $contact['office_line2']],
                    'person_name'   => ['text', 'Contact person name', $contact['person_name']],
                    'person_role'   => ['text', 'Contact person role', $contact['person_role']],
                    'phone'         => ['text', 'Phone', $contact['phone']],
                    'email'         => ['text', 'Email', $contact['email']],
                    'website'       => ['text', 'Website', $contact['website']],
                    'hours_weekday' => ['text', 'Business hours (Mon-Fri)', $contact['hours_weekday']],
                    'hours_weekend' => ['text', 'Business hours (Sat-Sun)', $contact['hours_weekend']],
                ],
            ],
            'gallery' => [
                'label' => 'Gallery',
                'url' => '/gallery',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <em>Gallery</em>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Events, projects, and moments from OGERA Agency'],
                ],
            ],
        ];
    }

    public static function pageSchema($page)
    {
        $schema = self::schema();

        return $schema[$page] ?? null;
    }

    /** Keys for editable content pages (Home, About, …). */
    public static function contentTabItems()
    {
        $items = [];
        foreach (self::schema() as $key => $page) {
            $items[$key] = $page['label'];
        }

        return $items;
    }

    /** Saved order of content page tabs in Site Content admin. */
    public static function contentTabOrder()
    {
        return SiteMenu::ordered('content_tabs_order', self::contentTabItems());
    }

    /** Page schema keyed by page, sorted for the admin tab bar. */
    public static function orderedSchema()
    {
        $schema = self::schema();
        $ordered = [];
        foreach (self::contentTabOrder() as $key) {
            if (isset($schema[$key])) {
                $ordered[$key] = $schema[$key];
            }
        }
        foreach ($schema as $key => $page) {
            if (! isset($ordered[$key])) {
                $ordered[$key] = $page;
            }
        }

        return $ordered;
    }
}
