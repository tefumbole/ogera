<?php

namespace App\Http\Controllers;

use App\Support\AppVersion;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;

/**
 * Renders the manual testing guide inside the admin area.
 *
 * The guide ships with the code rather than living in a shared document, so the
 * checklist a tester follows always matches the build they are testing.
 */
class TestingGuideController extends Controller
{
    const SOURCE = 'resources/docs/testing-guide.md';

    public function index()
    {
        $path = base_path(self::SOURCE);
        if (! is_readable($path)) {
            return view('setting.testing_guide', [
                'html' => null,
                'version' => AppVersion::erp(),
                'updatedAt' => null,
            ]);
        }

        $environment = Environment::createCommonMarkEnvironment();
        // Tables and `- [ ]` checkboxes are GitHub extensions, not core Markdown.
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $converter = new CommonMarkConverter([
            // The source is our own file, so raw HTML is safe, but there is none
            // to allow and escaping keeps it that way if the file ever changes.
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ], $environment);

        return view('setting.testing_guide', [
            'html' => $converter->convertToHtml(file_get_contents($path)),
            'version' => AppVersion::erp(),
            'updatedAt' => date('d M Y', filemtime($path)),
        ]);
    }
}
