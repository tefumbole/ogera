@php
    $card = \App\Support\GalleryEmbed::cardData($item);
    // Portrait embeds (TikTok / Instagram Reels) look wrong when cropped to
    // 4:3. Everything else stays uniform so the grid reads like a matrix.
    $portraitEmbed = in_array($item->type, ['tiktok', 'instagram', 'youtube_short'], true);
    $mediaAspect = $portraitEmbed ? 'og-media--portrait' : 'og-media--landscape';
@endphp

<div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col h-full">
    <div class="og-gallery-media {{ $mediaAspect }} relative bg-gray-100 overflow-hidden">
        @if ($item->type === 'image' && $card['file_url'])
            <img src="{{ $card['file_url'] }}" alt="{{ $item->title ?: 'Gallery image' }}" loading="lazy"
                 class="og-media-fill cursor-zoom-in gallery-lightbox-trigger"
                 data-full="{{ $card['file_url'] }}">
        @elseif ($item->type === 'video' && $card['file_url'])
            <video controls playsinline class="og-media-fill bg-black">
                <source src="{{ $card['file_url'] }}">
            </video>
        @elseif ($item->type === 'audio' && $card['file_url'])
            <div class="og-media-fill flex flex-col items-center justify-center p-6 text-center">
                <i data-lucide="music" class="w-16 h-16 text-brand-gold mb-4"></i>
                <audio controls class="w-full max-w-xs">
                    <source src="{{ $card['file_url'] }}">
                </audio>
            </div>
        @elseif (in_array($item->type, ['youtube', 'youtube_short']) && !empty($card['youtube_id']))
            <iframe class="og-media-fill" src="https://www.youtube.com/embed/{{ $card['youtube_id'] }}"
                    title="{{ $item->title ?: 'YouTube video' }}" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        @elseif ($item->type === 'tiktok' && !empty($card['tiktok_id']))
            <div class="og-media-fill flex items-center justify-center overflow-hidden">
                <blockquote class="tiktok-embed" cite="{{ $item->media_url }}" data-video-id="{{ $card['tiktok_id'] }}"
                            style="max-width:100%;min-width:280px;">
                    <section>
                        <a target="_blank" rel="noopener noreferrer" href="{{ $item->media_url }}">{{ $item->title ?: 'TikTok video' }}</a>
                    </section>
                </blockquote>
            </div>
        @elseif ($item->type === 'instagram' && !empty($card['instagram_path']))
            <div class="og-media-fill flex items-center justify-center overflow-hidden">
                <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/{{ $card['instagram_path'] }}/"
                            data-instgrm-version="14" style="max-width:100%;min-width:280px;width:100%;"></blockquote>
            </div>
        @elseif ($item->type === 'facebook' && $item->media_url)
            <iframe class="og-media-fill" src="https://www.facebook.com/plugins/video.php?href={{ urlencode($item->media_url) }}&show_text=false"
                    style="border:none;overflow:hidden" scrolling="no" frameborder="0"
                    allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
        @elseif ($item->media_url)
            <a href="{{ $item->media_url }}" target="_blank" rel="noopener"
               class="og-media-fill flex items-center justify-center text-brand-blue font-semibold text-center hover:underline p-6">
                View media <i data-lucide="external-link" class="w-4 h-4 inline"></i>
            </a>
        @else
            <div class="og-media-fill flex items-center justify-center text-gray-400">Media unavailable</div>
        @endif
    </div>

    @if ($item->title || $item->description || $item->type !== 'image')
        <div class="p-5 flex-grow">
            @if ($item->title)
                <h3 class="text-lg font-bold text-brand-blue mb-2">{{ $item->title }}</h3>
            @endif
            @if ($item->description)
                <p class="text-gray-600 text-sm leading-relaxed">{{ $item->description }}</p>
            @endif
            @if ($item->type !== 'image')
                <span class="inline-block mt-3 text-xs font-semibold uppercase tracking-wide text-brand-gold">
                    {{ \App\Support\GalleryEmbed::types()[$item->type] ?? $item->type }}
                </span>
            @endif
        </div>
    @endif
</div>
