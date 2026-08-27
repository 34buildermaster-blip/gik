<x-admin-layout title="จัดการสไลด์หน้าแรก | 34 Build Master Admin">
    <div class="topbar home-slide-heading">
        <div>
            <p class="eyebrow">Homepage Content</p>
            <h1>สไลด์หน้าแรก</h1>
            <p class="muted">เพิ่ม แก้ไข ลบ เปิดหรือปิด และจัดลำดับรูปที่แสดงบนหน้าแรกได้จากที่นี่</p>
        </div>
        <a class="button" href="{{ route('admin.home-slides.create', ['section' => 'hero']) }}">เพิ่มสไลด์ใหม่</a>
    </div>

    @foreach (\App\Models\HomeSlide::SECTION_LABELS as $section => $label)
        @php($sectionSlides = $slides->get($section, collect()))
        <section class="home-slide-section">
            <header class="home-slide-section-head">
                <div>
                    <p class="eyebrow">{{ $section === 'hero' ? 'TOP OF HOMEPAGE' : 'HOME STYLE CAROUSEL' }}</p>
                    <h2>{{ $label }}</h2>
                    <p>{{ $section === 'hero' ? 'ภาพและข้อความหลักด้านบนสุดของเว็บไซต์' : 'แกลเลอรีแบบบ้านในส่วน About Our Approach' }}</p>
                </div>
                <a class="button secondary" href="{{ route('admin.home-slides.create', ['section' => $section]) }}">
                    เพิ่ม{{ $section === 'hero' ? ' Hero Slide' : 'แบบบ้าน' }}
                </a>
            </header>

            <div class="home-slide-grid">
                @foreach ($sectionSlides as $slide)
                    <article class="home-slide-card {{ $slide->is_active ? '' : 'is-inactive' }}">
                        <div class="home-slide-preview">
                            @if ($slide->previewUrl())
                                <img src="{{ $slide->previewUrl() }}" alt="{{ $slide->alt_text }}">
                            @else
                                <span>ไม่มีรูปภาพ</span>
                            @endif
                            <span class="home-slide-order">ลำดับ {{ $slide->sort_order }}</span>
                            <span class="home-slide-status {{ $slide->is_active ? 'is-active' : '' }}">
                                {{ $slide->is_active ? 'แสดงอยู่' : 'ซ่อนอยู่' }}
                            </span>
                        </div>
                        <div class="home-slide-body">
                            <div>
                                @if ($slide->eyebrow)<small>{{ $slide->eyebrow }}</small>@endif
                                <h3>{{ $slide->title }}</h3>
                                @if ($slide->title_line_2)<p>{{ $slide->title_line_2 }}</p>@endif
                            </div>
                            <div class="home-slide-actions">
                                <a class="button secondary" href="{{ route('admin.home-slides.edit', $slide) }}">แก้ไข</a>
                                <form method="POST" action="{{ route('admin.home-slides.destroy', $slide) }}" onsubmit="return confirm('ต้องการลบสไลด์นี้ใช่ไหม? รูปที่อัปโหลดไว้จะถูกลบออกจากพื้นที่จัดเก็บด้วย')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button danger" type="submit">ลบ</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
</x-admin-layout>
