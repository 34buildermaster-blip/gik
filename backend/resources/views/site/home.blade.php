@extends('site.layout')
@section('title', $siteSettings['seo']['default_title'])
@section('description', $siteSettings['seo']['default_description'])
@section('body_class', 'home-page')
@section('content')
<section class="hero carousel carousel-fade" data-carousel data-interval="6500">
    <div class="carousel-track">
        @foreach($heroSlides as $slide)
            <article class="hero-slide {{ $loop->first ? 'active' : '' }}" data-slide>
                <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] ?? $slide['title'] }}" {{ $loop->first ? 'fetchpriority=high' : 'loading=lazy' }}>
                <div class="hero-shade"></div><div class="hero-content shell"><p class="eyebrow">{{ $slide['eyebrow'] }}</p><h1>{{ $slide['title'] }}@if($slide['title_line_2'])<br><span>{{ $slide['title_line_2'] }}</span>@endif</h1><p>{{ $slide['description'] }}</p><div><a class="button" href="{{ route('site.contact') }}">เริ่มต้นปรึกษา <span>→</span></a><a class="text-link text-link-light" href="{{ route('site.house-designs.index') }}">ดูแบบบ้านทั้งหมด <span>↗</span></a></div></div>
            </article>
        @endforeach
    </div>
    <div class="hero-controls shell"><button type="button" data-prev aria-label="สไลด์ก่อนหน้า">‹</button><div class="carousel-dots">@foreach($heroSlides as $slide)<button class="{{ $loop->first ? 'active' : '' }}" type="button" data-dot="{{ $loop->index }}" aria-label="สไลด์ {{ $loop->iteration }}"></button>@endforeach</div><button type="button" data-next aria-label="สไลด์ถัดไป">›</button></div>
</section>

<section class="promise-strip"><span>⌑ ออกแบบตามการใช้งานจริง</span><span>✓ ควบคุมคุณภาพเป็นขั้นตอน</span><span>▦ ดูแลครบตั้งแต่ต้นจนจบ</span><span>◌ ติดตามความคืบหน้าได้</span></section>

<section class="intro-section section shell">
    <div class="intro-copy reveal"><p class="eyebrow">ABOUT OUR APPROACH</p><h2>บ้านที่ดีเริ่มจากการเข้าใจ<br><span>สิ่งที่เจ้าของบ้านต้องการจริง ๆ</span></h2><div><p>เราเริ่มทุกโครงการด้วยการฟัง วางแผน และจัดลำดับความสำคัญ เพื่อให้งบประมาณ รูปแบบ และการใช้งานเดินไปในทิศทางเดียวกัน</p><a class="text-link" href="{{ route('site.about') }}">รู้จักแนวทางของเรา <span>→</span></a></div></div>
    <div class="approach-carousel carousel reveal" data-carousel data-interval="4200"><div class="carousel-track">@foreach($approachSlides as $slide)<figure class="approach-slide {{ $loop->first ? 'active' : '' }}" data-slide><img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] ?? ($slide['label'] ?? $slide['title']) }}" loading="lazy"><figcaption><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad((string) $loop->count, 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $slide['label'] ?? $slide['title'] }}</strong></figcaption></figure>@endforeach</div><div class="carousel-dots">@foreach($approachSlides as $slide)<button class="{{ $loop->first ? 'active' : '' }}" type="button" data-dot="{{ $loop->index }}" aria-label="ภาพ {{ $loop->iteration }}"></button>@endforeach</div></div>
</section>

@if($siteSettings['display']['show_home_services'])
<section class="services-section section"><div class="shell"><header class="section-heading reveal"><div><p class="eyebrow">OUR SERVICES</p><h2>บริการครบทุกขั้นตอน<br>เพื่อสร้างพื้นที่ในแบบของคุณ</h2></div><p>ลดความซับซ้อนของงานก่อสร้างด้วยทีมที่ดูแลทั้งภาพรวมและรายละเอียด ตั้งแต่คำปรึกษาแรกจนถึงวันส่งมอบ</p></header><div class="service-grid stagger">@foreach($services as $service)<a class="service-card" href="{{ route('site.services') }}"><div><span class="service-number">{{ $service['number'] }}</span><span class="service-arrow">↗</span></div><h3>{{ $service['title'] }}</h3><p>{{ $service['description'] }}</p><strong>ดูรายละเอียด →</strong></a>@endforeach</div></div></section>
@endif

@if($siteSettings['display']['show_home_projects'])
<section class="projects-section section"><div class="shell"><header class="section-heading reveal"><div><p class="eyebrow">SELECTED PROJECTS</p><h2>ผลงานที่สะท้อนวิธีคิด<br>และมาตรฐานของเรา</h2></div><a class="text-link" href="{{ route('site.house-designs.index') }}">ดูแบบบ้านทั้งหมด →</a></header><div class="project-grid stagger"><a class="project-card project-main" href="{{ route('site.house-designs.index') }}"><img src="{{ url('/selected-projects/tropical-japandi-exterior.webp') }}" alt="บ้าน Tropical Japandi Luxury"><div><span>RESIDENTIAL · CHIANG MAI</span><h3>บ้านพักอาศัยร่วมสมัย</h3><p>ออกแบบและก่อสร้าง</p></div></a><a class="project-card" href="{{ route('site.services') }}"><img src="{{ url('/selected-projects/tropical-japandi-living.webp') }}" alt="พื้นที่พักผ่อนภายใน"><div><span>INTERIOR · MATERIAL</span><h3>พื้นที่ภายในและบิวท์อิน</h3><p>ออกแบบรายละเอียดวัสดุ</p></div></a><a class="project-card project-copy" href="{{ route('site.services') }}"><span>RENOVATION</span><h3>เปลี่ยนพื้นที่เดิม<br>ให้กลับมาตอบโจทย์อีกครั้ง</h3><strong>ดูบริการรีโนเวท →</strong></a></div></div></section>
@endif

@if($siteSettings['display']['show_home_process'])
<section class="process-section section"><div class="shell"><header class="section-heading reveal"><div><p class="eyebrow">WORK PROCESS</p><h2>ขั้นตอนชัดเจน<br>เพื่อให้ทุกการตัดสินใจง่ายขึ้น</h2></div></header><div class="process-grid stagger">@foreach($process as $step)<article><span>{{ $step['number'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></article>@endforeach</div></div></section>
@endif

@if($siteSettings['display']['show_home_partners'])
<section class="brand-section section"><div class="shell"><header class="section-heading reveal"><div><p class="eyebrow">MATERIAL PARTNERS</p><h2>เลือกใช้วัสดุจากแบรนด์<br>ที่เป็นที่ยอมรับ</h2></div><p>พิจารณาวัสดุให้เหมาะกับงบประมาณ การใช้งาน และรายละเอียดของแต่ละโครงการ</p></header><div class="brand-grid stagger">@foreach($brands as $brand)<div><img src="{{ url($brand['logo']) }}" alt="โลโก้ {{ $brand['name'] }}" loading="lazy"><span>{{ $brand['category'] }}</span></div>@endforeach</div></div></section>
@endif

@if($siteSettings['display']['show_home_reviews'])
<section class="review-section section"><div class="shell"><header class="section-heading reveal"><div><p class="eyebrow">CLIENT EXPERIENCE</p><h2>ความมั่นใจที่เกิดจาก<br>การสื่อสารอย่างตรงไปตรงมา</h2></div></header></div><div class="review-carousel carousel reveal" data-review-carousel data-interval="5200"><div class="review-track">@foreach($testimonials as $review)<article class="review-card"><div class="review-person"><span class="review-avatar avatar-{{ $review['avatar'] }}"></span><div><strong>{{ $review['name'] }}</strong><span>{{ $review['project'] }}</span></div></div><div class="stars">★★★★★</div><blockquote>“{{ $review['text'] }}”</blockquote><small>{{ $review['location'] }}</small></article>@endforeach</div><div class="carousel-dots">@foreach($testimonials as $review)<button class="{{ $loop->first ? 'active' : '' }}" data-review-dot="{{ $loop->index }}" type="button" aria-label="รีวิว {{ $loop->iteration }}"></button>@endforeach</div></div></section>
@endif

@if($siteSettings['display']['show_home_contact']) @include('site.partials.contact') @endif
@endsection
