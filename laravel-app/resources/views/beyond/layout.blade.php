<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteLogoUrl = \App\Support\SiteBrand::logoUrl($general_setting ?? null);
        $siteTitle = \App\Support\SiteBrand::siteTitle($general_setting ?? null);
        $webUser = Auth::guard('web')->user();
        $beyondUser = Auth::guard('beyond')->user();
        $headerUser = $webUser ?: $beyondUser;
        $isAdminSession = (bool) $webUser;
        $headerName = $headerUser ? $headerUser->name : '';
        $headerRole = $isAdminSession
            ? 'ADMINISTRATOR'
            : strtoupper(str_replace('_', ' ', optional($beyondUser)->role ?: 'USER'));
        $headerInitial = $headerName !== '' ? mb_strtoupper(mb_substr($headerName, 0, 1)) : 'U';
        $shortName = \Illuminate\Support\Str::limit($headerName, 18, '…');
        $footer = \App\Support\OgeraLandingContent::footer();
        $isHome = request()->is('/');
    @endphp
    <title>@yield('title', $siteTitle) | {{ $siteTitle }}</title>
    <meta name="description" content="@yield('meta_description', 'OGERA Agency — business development, event management, and equipment rental in Kigali, Rwanda.')">
    <link rel="icon" href="{{ $siteLogoUrl }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#033D2E',
                            dark: '#071711',
                            light: '#07513D',
                            gold: '#D8AD4A',
                            navy: '#071711',
                        },
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    @php $ogeraPublicCssV = file_exists(public_path('css/ogera-public.css')) ? filemtime(public_path('css/ogera-public.css')) : 1; @endphp
    <link rel="stylesheet" href="{{ asset('public/css/ogera-public.css') }}?v={{ $ogeraPublicCssV }}">
    @stack('head')
</head>
<body class="ogera-public flex flex-col min-h-screen"
      x-data="{ open: false, userMenu: false, scrolled: false }"
      x-init="
        const onScroll = () => { scrolled = window.scrollY > 40 };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        $watch('open', v => { document.body.classList.toggle('ogera-body-lock', v); });
      "
      @keydown.escape.window="open = false; userMenu = false">

@php
    // Hardcoded OGERA public nav — do not pull from SiteMenu (admin Landing Menu stays independent).
    $navLinks = [
        ['label' => 'Home', 'url' => url('/'), 'match' => 'exact'],
        ['label' => 'About', 'url' => url('/about'), 'match' => 'path'],
        ['label' => 'Services', 'url' => url('/services'), 'match' => 'path'],
        ['label' => 'Events', 'url' => url('/events'), 'match' => 'path'],
        ['label' => 'Rentals', 'url' => url('/rentals'), 'match' => 'path'],
        ['label' => 'Gallery', 'url' => url('/gallery'), 'match' => 'path'],
        ['label' => 'Contact', 'url' => url('/contact'), 'match' => 'path'],
    ];
    $currentUrl = rtrim(url()->current(), '/');
    $homeUrl = rtrim(url('/'), '/');
@endphp

<header class="ogera-header" :class="{ 'is-solid': scrolled || open || {{ $isHome ? 'false' : 'true' }} }">
    <div class="ogera-header__inner">
        <a href="{{ url('/') }}" class="ogera-header__brand shrink-0">
            <img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}"
                 class="ogera-header__logo">
        </a>

        <nav class="ogera-header__nav" aria-label="Primary">
            @foreach ($navLinks as $link)
                @php
                    $linkUrl = rtrim(explode('#', $link['url'])[0], '/');
                    $active = false;
                    if (($link['match'] ?? '') === 'exact') {
                        $active = $currentUrl === $homeUrl || $currentUrl === '';
                    } elseif (($link['match'] ?? '') === 'path') {
                        $active = $currentUrl === $linkUrl || \Illuminate\Support\Str::startsWith($currentUrl, $linkUrl . '/');
                    } elseif (($link['match'] ?? '') === 'contact') {
                        $active = request()->is('about');
                    }
                @endphp
                <a href="{{ $link['url'] }}" class="ogera-header__link {{ $active ? 'is-active' : '' }}">{{ $link['label'] }}</a>
            @endforeach
            @unless ($headerUser)
                <a href="{{ url('/login') }}" class="ogera-header__link ogera-header__link--login {{ request()->is('login') ? 'is-active' : '' }}">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Login
                </a>
            @endunless
        </nav>

        <div class="hidden lg:flex items-center gap-3 shrink-0">
            @if ($headerUser)
                <div class="relative" @click.outside="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu"
                            class="flex items-center gap-2 pl-1 pr-1 py-1 rounded-md hover:bg-white/10 transition-colors text-left">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-[var(--og-gold)] bg-[var(--og-gold)] text-[var(--og-forest)] font-bold text-sm">
                            {{ $headerInitial }}
                        </span>
                        <span class="hidden xl:flex flex-col leading-tight min-w-0">
                            <span class="text-[var(--og-warm)] font-medium text-sm truncate max-w-[120px]">{{ $shortName }}</span>
                            <span class="text-[var(--og-gold)] text-[10px] font-semibold tracking-wide uppercase">{{ $headerRole }}</span>
                        </span>
                    </button>
                    <div x-show="userMenu" x-cloak x-transition
                         class="absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-xl border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2.5 text-sm font-bold text-gray-800">My Account</div>
                        <div class="border-t border-gray-100"></div>
                        @if ($isAdminSession)
                            <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">Admin Dashboard</a>
                            <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">Home Page</a>
                        @else
                            <a href="{{ url('/user/profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">My Profile</a>
                        @endif
                        <form method="POST" action="{{ $isAdminSession ? route('logout') : route('beyond.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">Logout</button>
                        </form>
                    </div>
                </div>
            @endif
            <a href="https://mail.hostinger.com" target="_blank" rel="noopener" class="ogera-header__icon" title="Webmail" aria-label="Email">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </a>
            <a href="{{ url('/contact') }}" class="ogera-header__cta">Start a Project</a>
        </div>

        <button type="button" class="ogera-header__menu-btn" @click="open = !open" :aria-expanded="open.toString()" aria-label="Menu">
            <i data-lucide="menu" class="w-5 h-5" x-show="!open"></i>
            <i data-lucide="x" class="w-5 h-5" x-show="open" x-cloak></i>
        </button>
    </div>

    <div class="ogera-drawer" x-show="open" x-cloak @click.self="open = false">
        <div class="ogera-drawer__panel" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <span class="text-[var(--og-gold)] text-sm tracking-[0.2em] uppercase">Menu</span>
                <button type="button" class="text-[var(--og-warm)]" @click="open = false" aria-label="Close">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}" @click="open = false">{{ $link['label'] }}</a>
            @endforeach
            @unless ($headerUser)
                <a href="{{ url('/login') }}" @click="open = false">Login</a>
            @endunless
            <a href="https://mail.hostinger.com" target="_blank" rel="noopener" @click="open = false">Webmail</a>
            <a href="{{ url('/contact') }}" @click="open = false" class="mt-3 !border-0 !text-[var(--og-forest)] !bg-[var(--og-gold)] text-center rounded-full py-3 font-medium">Start a Project</a>
            @if ($headerUser)
                <div class="mt-4 pt-4 border-t border-white/10 space-y-2">
                    <div class="text-[var(--og-warm)] text-sm font-medium">{{ $headerName }}</div>
                    @if ($isAdminSession)
                        <a href="{{ url('/admin') }}" @click="open = false">Admin Dashboard</a>
                    @else
                        <a href="{{ url('/user/profile') }}" @click="open = false">My Profile</a>
                    @endif
                    <form method="POST" action="{{ $isAdminSession ? route('logout') : route('beyond.logout') }}">
                        @csrf
                        <button type="submit" class="text-red-300 text-left w-full py-2">Logout</button>
                    </form>
                </div>
            @else
                <a href="{{ url('/login') }}" @click="open = false" class="mt-2 opacity-80">Login</a>
            @endif
        </div>
    </div>
</header>

<main class="flex-1">
    @yield('content')
</main>

<footer class="ogera-footer">
    <div class="ogera-footer__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <defs>
                <linearGradient id="ogeraWaveGold" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#e6c877"></stop>
                    <stop offset="30%" stop-color="#c1912b"></stop>
                    <stop offset="62%" stop-color="#dcb45a"></stop>
                    <stop offset="100%" stop-color="#f4dfa2"></stop>
                </linearGradient>
            </defs>
            <path fill="url(#ogeraWaveGold)" d="M0,74 C280,104 600,96 880,56 C1110,23 1290,10 1440,2 L1440,120 L0,120 Z"></path>
            <path class="ogera-wave__body" d="M0,90 C280,120 600,112 880,72 C1110,39 1290,26 1440,18 L1440,120 L0,120 Z"></path>
        </svg>
    </div>

    <div class="ogera-footer__body">
        <div class="ogera-container">
            <div class="ogera-footer__main">

                <div class="ogera-footer__qr">
                    <img src="{{ asset('public/branding/ogera-registration-qr-green.png') }}"
                         alt="OGERA Agency registration QR code">
                </div>

                <div class="ogera-footer__col">
                    <h3><i data-lucide="clipboard-list"></i> Services</h3>
                    <ul class="ogera-footer__list">
                        <li><a href="{{ url('/contact') }}?service=business">Business Development</a></li>
                        <li><a href="{{ url('/services') }}">Business Consultancy</a></li>
                    </ul>
                </div>

                <div class="ogera-footer__col">
                    <h3><i data-lucide="calendar-days"></i> Events &amp; Rentals</h3>
                    <ul class="ogera-footer__list">
                        <li><a href="{{ url('/events') }}">Event Planning &amp; Management</a></li>
                        <li><a href="{{ url('/rentals') }}">Rental Services</a></li>
                    </ul>
                </div>

                <div class="ogera-footer__col ogera-footer__col--contact">
                    <h3><i data-lucide="user"></i> Contact</h3>
                    <div class="ogera-footer__contact">
                        <a href="mailto:{{ $footer['email'] }}">
                            <i data-lucide="mail"></i><span>{{ $footer['email'] }}</span>
                        </a>
                        <a href="tel:{{ $footer['phone_tel'] }}">
                            <i data-lucide="phone"></i><span>{{ $footer['phone'] }}</span>
                        </a>
                        <a href="{{ $footer['website_url'] }}" target="_blank" rel="noopener">
                            <i data-lucide="globe"></i><span>{{ $footer['website'] }}</span>
                        </a>
                        <span class="ogera-footer__contact-item">
                            <i data-lucide="map-pin"></i><span>{{ $footer['address'] }}</span>
                        </span>
                    </div>
                </div>

            </div>

            <div class="ogera-footer__bottom">
                <p>© {{ date('Y') }} OGERA Agency. All rights reserved.</p>
                <p>
                    Developed By:
                    <a href="https://wa.me/250784006160" target="_blank" rel="noopener" style="display:inline;">Alpha Bridge Technologies</a>
                    | <a href="tel:+250784006160" style="display:inline;">+250 784 006 160</a>
                </p>
                <p>{{ \App\Support\AppVersion::bcl() }}</p>
            </div>
        </div>
    </div>
</footer>

<a href="https://wa.me/{{ $footer['wa'] }}" target="_blank" rel="noopener" class="ogera-wa" title="Chat on WhatsApp">
    <i data-lucide="message-circle" class="w-6 h-6"></i>
</a>

<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
    document.addEventListener('alpine:initialized', () => { if (window.lucide) lucide.createIcons(); });
</script>
@stack('scripts')
@include('components.whatsapp_phone_script')
<script>
(function () {
    if (window.__eventCountdownInit) return;
    window.__eventCountdownInit = true;
    function pad(n) { return n < 10 ? '0' + n : String(n); }
    function bindCountdown(el) {
        if (el.__countdownBound) return;
        el.__countdownBound = true;
        var targetIso = el.getAttribute('data-target');
        if (!targetIso) return;
        var hideAfter = el.getAttribute('data-hide-after') === '1';
        var doneMsg = el.querySelector('[data-done]');
        var units = el.querySelector('[data-units]');
        var target = new Date(targetIso).getTime();
        if (isNaN(target)) return;
        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) {
                if (units) units.classList.add('hidden');
                if (doneMsg) doneMsg.classList.remove('hidden');
                if (hideAfter) setTimeout(function () { el.style.display = 'none'; }, 8000);
                return false;
            }
            var secs = Math.floor(diff / 1000);
            var days = Math.floor(secs / 86400); secs %= 86400;
            var hours = Math.floor(secs / 3600); secs %= 3600;
            var mins = Math.floor(secs / 60); secs %= 60;
            var d = el.querySelector('.cd-days');
            var h = el.querySelector('.cd-hours');
            var m = el.querySelector('.cd-mins');
            var s = el.querySelector('.cd-secs');
            if (d) d.textContent = days;
            if (h) h.textContent = pad(hours);
            if (m) m.textContent = pad(mins);
            if (s) s.textContent = pad(secs);
            return true;
        }
        if (tick()) setInterval(tick, 1000);
    }
    document.querySelectorAll('[data-countdown]').forEach(bindCountdown);
})();
</script>
</body>
</html>
