{{-- Shared brand mark for client-facing rental agreement / portal pages --}}
@php
    $brandTitle = \App\Support\SiteBrand::siteTitle($general_setting ?? null);
    $brandLogoUrl = \App\Support\SiteBrand::logoUrl($general_setting ?? null);
@endphp
<style>
    .og-brand {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.22);
        margin: 0 auto 12px;
    }
    .og-brand img {
        width: 72px;
        height: 72px;
        object-fit: contain;
        display: block;
    }
    .og-brand--sm img {
        width: 48px;
        height: 48px;
    }
</style>
<div class="og-brand {{ !empty($compact) ? 'og-brand--sm' : '' }}">
    <img src="{{ $brandLogoUrl }}" alt="{{ $brandTitle }}">
</div>
