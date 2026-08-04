<?php

namespace App\Http\Controllers;

use App\Support\AppVersion;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;

/**
 * In-app user guide for staff. Chapters ship as Markdown beside the code so the
 * help a user sees always matches the build they are running.
 */
class UserHelpController extends Controller
{
    const DIR = 'resources/docs/help';

    /**
     * Core chapters first. Order here is the table of contents.
     * title / summary are used when a chapter file has no YAML front matter.
     */
    public static function chapters()
    {
        return [
            'getting-started' => [
                'title' => 'Getting Started',
                'summary' => 'Sign in, find your way around the sidebar, and open Help.',
                'icon' => 'dripicons-information',
            ],
            'products' => [
                'title' => 'Products',
                'summary' => 'Add products (including digital), print barcodes, and count stock.',
                'icon' => 'dripicons-list',
            ],
            'sales-pos' => [
                'title' => 'Sales & POS',
                'summary' => 'Make a sale at the counter, search by name or barcode, and print invoices.',
                'icon' => 'dripicons-cart',
            ],
            'bookings' => [
                'title' => 'Rental Module',
                'summary' => 'Create a booking, scan products in, send contracts, and set reminders.',
                'icon' => 'fa fa-exchange',
            ],
            'people' => [
                'title' => 'People & Customers',
                'summary' => 'Add customers and suppliers, and keep contact details up to date.',
                'icon' => 'dripicons-user',
            ],
            'announcements' => [
                'title' => 'Announcements & WhatsApp',
                'summary' => 'Compose, schedule, and send WhatsApp announcements and reminders.',
                'icon' => 'dripicons-broadcast',
            ],
            'settings' => [
                'title' => 'Settings Essentials',
                'summary' => 'Company details, timezone, messaging, users, and roles.',
                'icon' => 'dripicons-gear',
            ],
        ];
    }

    public function index()
    {
        return view('help.index', [
            'chapters' => self::chapters(),
            'version' => AppVersion::erp(),
        ]);
    }

    public function show($slug)
    {
        $chapters = self::chapters();
        if (! isset($chapters[$slug])) {
            abort(404);
        }

        $path = base_path(self::DIR.'/'.$slug.'.md');
        $html = null;
        $updatedAt = null;

        if (is_readable($path)) {
            $environment = Environment::createCommonMarkEnvironment();
            $environment->addExtension(new GithubFlavoredMarkdownExtension());
            $converter = new CommonMarkConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ], $environment);

            $markdown = file_get_contents($path);
            // Strip a leading YAML front-matter block if present.
            $markdown = preg_replace('/\A---\s*\n.*?\n---\s*\n/s', '', $markdown, 1);
            $html = $converter->convertToHtml($markdown);
            $updatedAt = date('d M Y', filemtime($path));
        }

        $slugs = array_keys($chapters);
        $index = array_search($slug, $slugs, true);
        $prev = $index > 0 ? $slugs[$index - 1] : null;
        $next = ($index !== false && $index < count($slugs) - 1) ? $slugs[$index + 1] : null;

        return view('help.show', [
            'chapters' => $chapters,
            'slug' => $slug,
            'chapter' => $chapters[$slug],
            'html' => $html,
            'version' => AppVersion::erp(),
            'updatedAt' => $updatedAt,
            'prevSlug' => $prev,
            'nextSlug' => $next,
        ]);
    }
}
