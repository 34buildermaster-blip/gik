<x-admin-layout title="จัดการแบบบ้าน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">House Design CMS</p>
            <h1>จัดการแบบบ้าน</h1>
            <p class="muted" style="margin: 7px 0 0;">เพิ่ม แก้ไข ลบ และควบคุมข้อมูลหน้า Detail รวมถึงภาพปกและแกลเลอรี</p>
        </div>
        <a class="button" href="{{ route('admin.house-designs.create') }}">เพิ่มแบบบ้าน</a>
    </div>

    <section class="house-admin-stats">
        <div><span>แบบบ้านทั้งหมด</span><strong>{{ $designs->total() }}</strong></div>
        <div><span>เผยแพร่แล้ว</span><strong>{{ $publishedCount }}</strong></div>
        <div><span>ฉบับร่าง</span><strong>{{ $draftCount }}</strong></div>
    </section>

    <section class="card panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองแบบบ้าน">
                <a class="filter-chip {{ $status === '' && $style === '' ? 'is-active' : '' }}" href="{{ route('admin.house-designs.index', array_filter(['q' => $search])) }}">ทั้งหมด</a>
                @foreach (\App\Models\HouseDesign::STATUS_LABELS as $key => $label)
                    <a class="filter-chip {{ $status === $key ? 'is-active' : '' }}" href="{{ route('admin.house-designs.index', array_filter(['q' => $search, 'status' => $key, 'style' => $style])) }}">{{ $label }}</a>
                @endforeach
                @foreach (\App\Models\HouseDesign::STYLE_LABELS as $key => $label)
                    <a class="filter-chip {{ $style === $key ? 'is-active' : '' }}" href="{{ route('admin.house-designs.index', array_filter(['q' => $search, 'status' => $status, 'style' => $key])) }}">{{ $label }}</a>
                @endforeach
            </div>
            <p class="result-note">{{ $designs->total() }} รายการ</p>
        </div>

        <div class="house-admin-grid">
            @forelse ($designs as $design)
                <article class="house-admin-card">
                    <div class="house-admin-cover">
                        @if ($design->previewCoverUrl())
                            <img src="{{ $design->previewCoverUrl() }}" alt="{{ $design->cover_alt }}">
                        @else
                            <span>ไม่มีรูปปก</span>
                        @endif
                        <span class="house-admin-status {{ $design->status === 'published' ? 'is-published' : '' }}">
                            {{ \App\Models\HouseDesign::STATUS_LABELS[$design->status] ?? $design->status }}
                        </span>
                    </div>
                    <div class="house-admin-body">
                        <p>{{ $design->name }} · {{ \App\Models\HouseDesign::STYLE_LABELS[$design->style] ?? $design->style }}</p>
                        <h2>{{ $design->title }}</h2>
                        <dl>
                            <div><dt>พื้นที่</dt><dd>{{ number_format($design->area) }} ตร.ม.</dd></div>
                            <div><dt>ห้องนอน</dt><dd>{{ $design->bedrooms }}</dd></div>
                            <div><dt>แกลเลอรี</dt><dd>{{ $design->images_count }} รูป</dd></div>
                        </dl>
                        <div class="house-admin-actions">
                            <a class="button secondary" href="{{ rtrim(config('app.frontend_url'), '/') }}/house-designs/{{ $design->slug }}" target="_blank" rel="noreferrer">ดูหน้าเว็บ</a>
                            <a class="button secondary" href="{{ route('admin.house-designs.edit', $design) }}">แก้ไข</a>
                            <form method="POST" action="{{ route('admin.house-designs.destroy', $design) }}" onsubmit="return confirm('ลบแบบบ้านนี้และรูปที่อัปโหลดทั้งหมดใช่ไหม?')">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">ลบ</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="house-admin-empty">
                    <strong>ยังไม่มีแบบบ้านในรายการนี้</strong>
                    <p>เพิ่มแบบบ้านใหม่เพื่อเริ่มสร้างหน้ารวมและหน้า Detail</p>
                </div>
            @endforelse
        </div>

        <div class="pagination">{{ $designs->links() }}</div>
    </section>
</x-admin-layout>
