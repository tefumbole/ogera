@extends('beyond.layout')

@section('title', 'Gallery | OGERA Agency')
@section('meta_description', 'Explore events, projects, and highlights from OGERA Agency — photos and videos from our work in Kigali.')

@push('head')
<style>
    /* Uniform media tiles: every card's media area is a fixed aspect ratio and
       the underlying image / video / iframe fills it via object-cover. This
       is what the admin gallery already does — the public gallery now matches. */
    .og-gallery-media {
        position: relative;
        width: 100%;
        background: #f4f7fb;
    }
    .og-gallery-media::before {
        content: "";
        display: block;
    }
    .og-media--landscape::before { padding-top: 75%; }  /* 4:3 */
    .og-media--portrait::before  { padding-top: 133.333%; } /* 3:4 for TikTok / Reels */

    .og-media-fill {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 0;
    }
    /* Video files and iframes shouldn't crop the actual player controls out;
       object-cover on <video> keeps behaviour identical to <img>. */
    video.og-media-fill,
    iframe.og-media-fill {
        object-fit: cover;
        background: #000;
    }
    /* Embed blockquotes (TikTok / Instagram) need to scroll inside the tile
       rather than stretch the card. */
    .og-media-fill blockquote { max-height: 100%; }
</style>
@endpush

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::html('gallery.hero_title', 'Our <em>Gallery</em>'),
    'subtitle' => \App\Support\SiteContent::text('gallery.hero_subtitle', 'Events, projects, and moments from OGERA Agency'),
])

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($items->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <i data-lucide="image" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p class="text-lg">Gallery items will appear here once added in Site Content.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($items as $item)
                    @include('beyond.partials.gallery_item', ['item' => $item])
                @endforeach
            </div>
        @endif
    </div>
</section>

<div id="gallery-lightbox" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" onclick="closeGalleryLightbox(event)">
    <button type="button" class="absolute top-4 right-4 text-white text-3xl leading-none" onclick="closeGalleryLightbox(event)">&times;</button>
    <img id="gallery-lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
</div>

@endsection

@push('scripts')
<script async src="https://www.tiktok.com/embed.js"></script>
<script async src="//www.instagram.com/embed.js"></script>
<script>
document.querySelectorAll('.gallery-lightbox-trigger').forEach(function (img) {
    img.addEventListener('click', function () {
        var lb = document.getElementById('gallery-lightbox');
        var full = document.getElementById('gallery-lightbox-img');
        full.src = img.getAttribute('data-full');
        full.alt = img.alt;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
    });
});
function closeGalleryLightbox(e) {
    if (e.target.id === 'gallery-lightbox' || e.target.tagName === 'BUTTON') {
        var lb = document.getElementById('gallery-lightbox');
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        document.getElementById('gallery-lightbox-img').src = '';
    }
}
</script>
@endpush
