<?php

namespace App\Http\Controllers;

use App\SiteReview;
use App\Support\Reviews;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin management of customer reviews. Lives under the Site Content
 * permission so anyone who already edits the public pages can moderate the
 * reviews that appear on them.
 */
class ReviewController extends Controller
{
    protected function authorizeAdmin()
    {
        if (! Auth::check() || ! RoleAccess::allows('site_content')) {
            abort(403, 'You are not allowed to manage reviews.');
        }
    }

    /** Landing page → the Site Content tab, so the sidebar entry and the
     *  in-page tab always show the same thing. */
    public function index()
    {
        return redirect('/admin/site-content?tab=reviews');
    }

    /**
     * Admin-created review: skips the public form's moderation threshold and
     * goes live straight away by default, since a signed-in admin is trusted.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $this->validated($request);
        $data['source'] = 'admin';
        $data['is_public'] = (string) $request->input('is_public', '1') === '1';
        $data['is_pinned'] = (string) $request->input('is_pinned', '0') === '1';
        $data['sort_order'] = ((int) SiteReview::max('sort_order')) + 1;

        SiteReview::create($data);

        return $this->back('Review added.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $review = SiteReview::findOrFail($id);
        $data = $this->validated($request);
        $data['is_public'] = (string) $request->input('is_public', '0') === '1';
        $data['is_pinned'] = (string) $request->input('is_pinned', '0') === '1';

        if ($request->filled('admin_reply')) {
            $data['admin_reply'] = trim((string) $request->input('admin_reply'));
            if ($data['admin_reply'] !== ($review->admin_reply ?? '')) {
                $data['replied_at'] = now();
            }
        } else {
            $data['admin_reply'] = null;
            $data['replied_at'] = null;
        }

        $review->update($data);

        return $this->back('Review updated.');
    }

    /** Publish / unpublish toggle used from the moderation queue and the list. */
    public function togglePublic(Request $request, $id)
    {
        $this->authorizeAdmin();
        $review = SiteReview::findOrFail($id);
        $review->is_public = ! $review->is_public;
        $review->save();

        return $this->back($review->is_public ? 'Review published.' : 'Review hidden.');
    }

    public function togglePinned(Request $request, $id)
    {
        $this->authorizeAdmin();
        $review = SiteReview::findOrFail($id);
        $review->is_pinned = ! $review->is_pinned;
        $review->save();

        return $this->back($review->is_pinned ? 'Review pinned.' : 'Pin removed.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();
        SiteReview::whereKey($id)->delete();

        return $this->back('Review removed.');
    }

    public function reorder(Request $request)
    {
        $this->authorizeAdmin();
        $order = array_map('intval', (array) $request->input('order', []));
        foreach ($order as $pos => $id) {
            SiteReview::where('id', $id)->update(['sort_order' => $pos]);
        }

        return $this->back('Order saved.');
    }

    /** Save the tab-level settings (enabled, outbound CTA, moderation threshold). */
    public function saveSettings(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'enabled' => 'nullable',
            'outbound_cta' => 'nullable',
            'hold_below' => 'nullable|integer|min:1|max:5',
            'headline' => 'nullable|string|max:255',
            'subtext' => 'nullable|string|max:1000',
        ]);

        Reviews::setSetting('enabled', $request->has('enabled') ? '1' : '0');
        Reviews::setSetting('outbound_cta', $request->has('outbound_cta') ? '1' : '0');
        Reviews::setSetting('hold_below', (int) ($data['hold_below'] ?? Reviews::DEFAULT_HOLD_BELOW));
        \App\Support\SiteContent::put('reviews.headline', $data['headline'] ?? '');
        \App\Support\SiteContent::put('reviews.subtext', $data['subtext'] ?? '');

        return $this->back('Review settings saved.');
    }

    protected function validated(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'message' => 'required|string|max:4000',
            'reference' => 'nullable|string|max:100',
            'customer_id' => 'nullable|integer',
        ]);

        return [
            'name' => trim($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'country' => isset($data['country']) ? trim($data['country']) : null,
            'rating' => (int) $data['rating'],
            'title' => isset($data['title']) ? trim($data['title']) : null,
            'message' => trim($data['message']),
            'reference' => $data['reference'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
        ];
    }

    protected function back($message)
    {
        return redirect('/admin/site-content?tab=reviews')->with('message', $message);
    }
}
