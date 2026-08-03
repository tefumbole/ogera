<?php

namespace App\Http\Controllers;

use App\SiteReview;
use App\Support\Reviews;
use App\Support\SiteContent;
use Illuminate\Http\Request;

class PublicReviewController extends Controller
{
    public function index()
    {
        if (! Reviews::isEnabled()) {
            abort(404);
        }

        $reviews = SiteReview::public()->ordered()->get();

        // Rating summary — only over public reviews so the number matches what
        // visitors can actually see below it.
        $count = $reviews->count();
        $average = $count > 0 ? round($reviews->avg('rating'), 1) : null;
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $r) {
            $key = max(1, min(5, (int) $r->rating));
            $distribution[$key]++;
        }

        return view('beyond.reviews', [
            'reviews' => $reviews,
            'summary' => [
                'count' => $count,
                'average' => $average,
                'distribution' => $distribution,
            ],
            'headline' => SiteContent::text('reviews.headline', 'Client Reviews'),
            'subtext' => SiteContent::text('reviews.subtext', 'Real words from the people we have worked with.'),
            'holdBelow' => Reviews::holdBelow(),
        ]);
    }

    public function store(Request $request)
    {
        if (! Reviews::isEnabled()) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'message' => 'required|string|max:4000',
            'reference' => 'nullable|string|max:100',
            // Anti-spam: filled by bots that fill every field.
            'website' => 'nullable|max:0',
        ], [
            'website.max' => 'Spam detected.',
        ]);

        $rating = (int) $data['rating'];

        // Ratings below the moderation threshold arrive as pending so a real
        // person can decide whether they should appear publicly.
        SiteReview::create([
            'name' => trim($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'country' => isset($data['country']) ? trim($data['country']) : null,
            'rating' => $rating,
            'title' => isset($data['title']) ? trim($data['title']) : null,
            'message' => trim($data['message']),
            'reference' => $data['reference'] ?? null,
            'source' => 'public',
            'is_public' => Reviews::shouldAutoPublish($rating),
            'ip' => $request->ip(),
        ]);

        $flash = Reviews::shouldAutoPublish($rating)
            ? 'Thanks — your review is live on the site.'
            : 'Thanks — your review has been received and will appear once approved.';

        return redirect(url('reviews').'#form')->with('message', $flash);
    }
}
