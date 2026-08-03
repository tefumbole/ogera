<div class="rv-card {{ $review->is_public ? 'is-public' : 'is-pending' }} {{ $review->is_pinned ? 'is-pinned' : '' }}">
    <div class="rv-card__top">
        <div>
            <span class="rv-card__who">{{ $review->name }}</span>
            @if($review->email) <span class="rv-card__meta">· {{ $review->email }}</span>@endif
            @if($review->phone) <span class="rv-card__meta">· {{ \App\Support\WhatsAppPhone::display($review->phone) }}</span>@endif
        </div>
        <div class="rv-card__stars" title="{{ $review->rating }}/5">
            {{ str_repeat('★', (int) $review->rating) }}<span class="dim">{{ str_repeat('★', 5 - (int) $review->rating) }}</span>
        </div>
    </div>
    <div class="rv-card__meta">
        {{ optional($review->created_at)->format('d M Y, H:i') }}
        @if($review->source) · {{ ucfirst($review->source) }} @endif
        @if($review->country) · {{ $review->country }} @endif
        @if($review->reference) · Ref {{ $review->reference }} @endif
    </div>
    @if($review->title)
        <div class="rv-card__title">{{ $review->title }}</div>
    @endif
    <div class="rv-card__body">{{ $review->message }}</div>
    @if($review->admin_reply)
        <div class="rv-card__reply">
            <strong style="display:block; color:#033d2e; margin-bottom:2px;">Your reply</strong>
            {{ $review->admin_reply }}
        </div>
    @endif

    <div class="rv-card__actions">
        <form method="POST" action="{{ route('reviews.publish', $review->id) }}">
            @csrf
            @if($review->is_public)
                <button type="submit" class="btn btn-outline-secondary btn-sm">Hide</button>
            @else
                <button type="submit" class="btn btn-success btn-sm">Publish</button>
            @endif
        </form>
        <form method="POST" action="{{ route('reviews.pin', $review->id) }}">
            @csrf
            <button type="submit" class="btn btn-outline-warning btn-sm">
                {{ $review->is_pinned ? 'Unpin' : 'Pin to top' }}
            </button>
        </form>
        <button type="button" class="btn btn-outline-primary btn-sm rv-edit-toggle" data-target="rv-edit-{{ $review->id }}">Edit</button>
        <form method="POST" action="{{ route('reviews.destroy', $review->id) }}" onsubmit="return confirm('Delete this review permanently?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
    </div>

    <div class="rv-edit" id="rv-edit-{{ $review->id }}">
        <form method="POST" action="{{ route('reviews.update', $review->id) }}">
            @csrf
            <div class="form-group">
                <label class="small font-weight-bold">Name</label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ $review->name }}" required>
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Rating</label>
                <select name="rating" class="form-control form-control-sm" style="max-width:160px;">
                    @foreach([1,2,3,4,5] as $s)
                        <option value="{{ $s }}" {{ (int) $review->rating === $s ? 'selected' : '' }}>{{ $s }} star{{ $s === 1 ? '' : 's' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Country</label>
                <input type="text" name="country" class="form-control form-control-sm" value="{{ $review->country }}">
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Headline</label>
                <input type="text" name="title" class="form-control form-control-sm" value="{{ $review->title }}">
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Review</label>
                <textarea name="message" rows="3" class="form-control form-control-sm" required>{{ $review->message }}</textarea>
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Reply (public)</label>
                <textarea name="admin_reply" rows="2" class="form-control form-control-sm" placeholder="Thank the reviewer or clarify anything for future visitors">{{ $review->admin_reply }}</textarea>
            </div>
            <input type="hidden" name="email" value="{{ $review->email }}">
            <input type="hidden" name="phone" value="{{ $review->phone }}">
            <input type="hidden" name="reference" value="{{ $review->reference }}">
            <label class="d-flex align-items-center mb-2" style="gap:8px;">
                <input type="checkbox" name="is_public" value="1" @if($review->is_public) checked @endif> Published
            </label>
            <label class="d-flex align-items-center mb-2" style="gap:8px;">
                <input type="checkbox" name="is_pinned" value="1" @if($review->is_pinned) checked @endif> Pinned to top
            </label>
            <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
        </form>
    </div>
</div>
