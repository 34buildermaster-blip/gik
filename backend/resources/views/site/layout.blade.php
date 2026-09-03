<!doctype html>
<html lang="th">
<head>
    @php
        $general = $siteSettings['general'];
        $social = $siteSettings['social'];
        $branding = $siteSettings['branding'];
        $seo = $siteSettings['seo'];
        $navigation = $siteSettings['navigation'];
        $pageTitle = trim($__env->yieldContent('title')) ?: $seo['default_title'];
        $pageDescription = trim($__env->yieldContent('description')) ?: $seo['default_description'];
        $logoUrl = $branding['logo_url'] ?: url('/brand-logo.webp');
        $footerLogoUrl = $branding['footer_logo_url'] ?: $logoUrl;
        $ogImage = trim($__env->yieldContent('og_image')) ?: ($seo['og_image_url'] ?: $logoUrl);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="theme-color" content="#123d2d">
    <link rel="icon" href="{{ $branding['favicon_url'] ?: $logoUrl }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/css/site.min.css') }}?v=2">
    <script>document.documentElement.classList.add('js')</script>
    @stack('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'GeneralContractor',
        'name' => $general['company_name_en'], 'url' => url('/'), 'logo' => $logoUrl,
        'telephone' => $general['phone_display'], 'email' => $general['email'],
        'address' => $general['address'], 'areaServed' => $general['service_area'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="site-body @yield('body_class')">
    <a class="skip-link" href="#main-content">ข้ามไปยังเนื้อหา</a>
    <header class="site-header" data-site-header>
        <div class="topbar shell">
            <nav aria-label="ลิงก์เสริม"><a href="{{ route('site.faq') }}">คำถามที่พบบ่อย</a><a href="{{ route('site.blog.index') }}">บทความ</a><a href="{{ route('site.updates') }}">อัปเดตหน้างาน</a></nav>
            <div><a href="{{ $general['phone_href'] }}">โทร {{ $general['phone_display'] }}</a><a href="{{ $social['line_url'] }}" target="_blank" rel="noreferrer">LINE OA</a></div>
        </div>
        <div class="nav-wrap">
            <div class="main-nav shell">
                <a class="brand" href="{{ route('site.home') }}" aria-label="หน้าหลัก {{ $general['company_name_en'] }}">
                    <img src="{{ $logoUrl }}" alt="โลโก้ {{ $general['company_name_en'] }}">
                    <span><strong>BUILD MASTER</strong><small>CONSTRUCTION</small></span>
                </a>
                <button class="nav-toggle" type="button" aria-label="เปิดเมนู" aria-expanded="false" data-nav-toggle><span></span><span></span><span></span></button>
                <nav class="nav-links" aria-label="เมนูหลัก" data-nav-menu>
                    <a class="{{ request()->routeIs('site.home') ? 'active' : '' }}" href="{{ route('site.home') }}">หน้าหลัก</a>
                    <a class="{{ request()->routeIs('site.about') ? 'active' : '' }}" href="{{ route('site.about') }}">เกี่ยวกับเรา</a>
                    <a class="{{ request()->routeIs('site.services') ? 'active' : '' }}" href="{{ route('site.services') }}">บริการ</a>
                    @if($navigation['show_house_designs'])<a class="{{ request()->routeIs('site.house-designs.*') ? 'active' : '' }}" href="{{ route('site.house-designs.index') }}">แบบบ้าน</a>@endif
                    @if($navigation['show_updates'])<a class="{{ request()->routeIs('site.updates') ? 'active' : '' }}" href="{{ route('site.updates') }}">อัปเดตงาน</a>@endif
                    @if($navigation['show_blog'])<a class="{{ request()->routeIs('site.blog.*') ? 'active' : '' }}" href="{{ route('site.blog.index') }}">บทความ</a>@endif
                    @if($navigation['show_faq'])<a class="{{ request()->routeIs('site.faq') ? 'active' : '' }}" href="{{ route('site.faq') }}">FAQ</a>@endif
                    <a class="{{ request()->routeIs('site.contact') ? 'active' : '' }}" href="{{ route('site.contact') }}">ติดต่อ</a>
                    <a class="nav-login" href="{{ route('login.customer') }}">ติดตามความคืบหน้า</a>
                    <a class="button button-small" href="{{ route('site.contact') }}">{{ $siteSettings['cta']['consultation_label'] }}</a>
                </nav>
            </div>
        </div>
    </header>

    <main id="main-content">@yield('content')</main>

    <footer class="site-footer">
        <div class="footer-grid shell">
            <div class="footer-brand"><a class="brand brand-light" href="{{ route('site.home') }}"><img src="{{ $footerLogoUrl }}" alt=""><span><strong>BUILD MASTER</strong><small>CONSTRUCTION</small></span></a><p>{{ $general['tagline'] }}</p></div>
            <div><h2>เมนู</h2><a href="{{ route('site.about') }}">เกี่ยวกับเรา</a><a href="{{ route('site.services') }}">บริการ</a><a href="{{ route('site.house-designs.index') }}">แบบบ้าน</a><a href="{{ route('site.blog.index') }}">บทความ</a></div>
            <div><h2>ติดต่อ</h2><a href="{{ $general['phone_href'] }}">{{ $general['phone_display'] }}</a><a href="mailto:{{ $general['email'] }}">{{ $general['email'] }}</a><p>{{ $general['address'] }}</p></div>
            <div><h2>ติดตามเรา</h2><div class="social-row"><a href="{{ $social['facebook_url'] }}" target="_blank" rel="noreferrer" aria-label="Facebook">f</a><a href="{{ $social['instagram_url'] }}" target="_blank" rel="noreferrer" aria-label="Instagram">ig</a><a href="{{ $social['line_url'] }}" target="_blank" rel="noreferrer" aria-label="LINE">LINE</a><a href="{{ $social['tiktok_url'] }}" target="_blank" rel="noreferrer" aria-label="TikTok">tt</a></div><button class="cookie-settings-link" type="button" data-cookie-settings>ตั้งค่าคุกกี้</button></div>
        </div>
        <div class="footer-bottom shell"><span>{{ $general['copyright'] }}</span><span><a href="{{ route('legal.privacy') }}">นโยบายความเป็นส่วนตัว</a><a href="{{ route('legal.terms') }}">ข้อกำหนดการใช้งาน</a></span></div>
    </footer>

    <aside class="contact-dock" aria-label="ช่องทางติดต่อด่วน"><a href="{{ $general['phone_href'] }}" aria-label="โทรศัพท์">☎</a><a href="{{ $social['line_url'] }}" target="_blank" rel="noreferrer" aria-label="LINE">LINE</a><a href="{{ $social['facebook_url'] }}" target="_blank" rel="noreferrer" aria-label="Facebook">f</a></aside>

    <section class="cookie-banner" data-cookie-banner hidden aria-label="การตั้งค่าคุกกี้"><div><strong>เว็บไซต์นี้ใช้คุกกี้</strong><p>เราใช้คุกกี้ที่จำเป็นเพื่อให้เว็บไซต์ทำงาน และคุกกี้วิเคราะห์เมื่อคุณอนุญาต</p></div><div><button class="button button-ghost" type="button" data-cookie-reject>เฉพาะที่จำเป็น</button><button class="button" type="button" data-cookie-accept>ยอมรับทั้งหมด</button></div></section>

    @if($sitePopup && $sitePopup['desktop_image'])
        <div class="welcome-popup" data-welcome-popup data-popup-key="welcome-{{ $sitePopup['id'] }}-{{ $sitePopup['updated_at'] }}" hidden>
            <button type="button" aria-label="ปิดป๊อปอัป" data-popup-close>×</button>
            @if($sitePopup['link_url'])<a href="{{ $sitePopup['link_url'] }}">@endif
                <picture>@if($sitePopup['mobile_image'])<source media="(max-width: 640px)" srcset="{{ $sitePopup['mobile_image'] }}">@endif<img src="{{ $sitePopup['desktop_image'] }}" alt="{{ $sitePopup['alt'] ?: 'ข่าวสารจาก 34 Build Master' }}"></picture>
            @if($sitePopup['link_url'])</a>@endif
        </div>
    @endif

    <script src="{{ url('/js/site.js') }}?v=2" defer></script>
    @stack('scripts')
</body>
</html>
