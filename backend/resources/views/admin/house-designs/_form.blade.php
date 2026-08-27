@php
    $isEdit = $design->exists;
    $formAction = $isEdit ? route('admin.house-designs.update', $design) : route('admin.house-designs.store');
@endphp

<form class="house-design-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="card panel house-form-section">
        <header>
            <span>01</span>
            <div><h2>ข้อมูลหลัก</h2><p>ชื่อ URL สไตล์ งบประมาณ และสถานะการเผยแพร่</p></div>
        </header>
        <div class="form-grid">
            <div class="field">
                <label for="name">ชื่อแบบภาษาอังกฤษ</label>
                <input id="name" name="name" type="text" maxlength="160" value="{{ old('name', $design->name) }}" placeholder="BM Courtyard" required>
            </div>
            <div class="field">
                <label for="title">ชื่อแบบภาษาไทย</label>
                <input id="title" name="title" type="text" maxlength="255" value="{{ old('title', $design->title) }}" placeholder="บ้านโมเดิร์นคอร์ทยาร์ด" required>
            </div>
            <div class="field">
                <label for="slug">Slug สำหรับ URL</label>
                <input id="slug" name="slug" type="text" maxlength="255" value="{{ old('slug', $design->slug) }}" placeholder="เว้นว่างเพื่อสร้างจากชื่อภาษาอังกฤษ">
            </div>
            <div class="field">
                <label for="style">สไตล์บ้าน</label>
                <select id="style" name="style" required>
                    @foreach (\App\Models\HouseDesign::STYLE_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('style', $design->style) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="budget_category">ช่วงงบสำหรับตัวกรอง</label>
                <select id="budget_category" name="budget_category" required>
                    @foreach (\App\Models\HouseDesign::BUDGET_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('budget_category', $design->budget_category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="budget_label">งบประมาณที่แสดง</label>
                <input id="budget_label" name="budget_label" type="text" maxlength="120" value="{{ old('budget_label', $design->budget_label) }}" placeholder="5.8 - 7.2 ล้านบาท" required>
            </div>
            <div class="field">
                <label for="status">สถานะ</label>
                <select id="status" name="status" required>
                    @foreach (\App\Models\HouseDesign::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $design->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="sort_order">ลำดับการแสดง</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $design->sort_order) }}" required>
            </div>
        </div>
    </section>

    <section class="card panel house-form-section">
        <header>
            <span>02</span>
            <div><h2>สเปกแบบบ้าน</h2><p>ข้อมูลสรุปที่แสดงบนการ์ดและหน้า Detail</p></div>
        </header>
        <div class="house-spec-inputs">
            <div class="field"><label for="area">พื้นที่ใช้สอย (ตร.ม.)</label><input id="area" name="area" type="number" min="20" max="5000" value="{{ old('area', $design->area) }}" required></div>
            <div class="field"><label for="bedrooms">ห้องนอน</label><input id="bedrooms" name="bedrooms" type="number" min="0" max="30" value="{{ old('bedrooms', $design->bedrooms) }}" required></div>
            <div class="field"><label for="bathrooms">ห้องน้ำ</label><input id="bathrooms" name="bathrooms" type="number" min="0" max="30" value="{{ old('bathrooms', $design->bathrooms) }}" required></div>
            <div class="field"><label for="floors">จำนวนชั้น</label><input id="floors" name="floors" type="number" min="1" max="10" value="{{ old('floors', $design->floors) }}" required></div>
            <div class="field"><label for="parking_spaces">ที่จอดรถ</label><input id="parking_spaces" name="parking_spaces" type="number" min="0" max="30" value="{{ old('parking_spaces', $design->parking_spaces) }}" required></div>
        </div>
    </section>

    <section class="card panel house-form-section">
        <header>
            <span>03</span>
            <div><h2>เนื้อหาหน้า Detail</h2><p>อธิบายภาพรวม แนวคิด และจุดเด่นของแบบบ้าน</p></div>
        </header>
        <div class="form-grid">
            <div class="field full">
                <label for="description">คำอธิบายภาพรวม</label>
                <textarea id="description" name="description" rows="5" maxlength="3000" required>{{ old('description', $design->description) }}</textarea>
            </div>
            <div class="field full">
                <label for="concept">แนวคิดการออกแบบ</label>
                <textarea id="concept" name="concept" rows="6" maxlength="6000">{{ old('concept', $design->concept) }}</textarea>
            </div>
            <div class="field full">
                <label for="features_text">จุดเด่นของแบบบ้าน</label>
                <textarea id="features_text" name="features_text" rows="6" maxlength="5000" placeholder="ใส่ 1 จุดเด่นต่อ 1 บรรทัด">{{ old('features_text', implode(PHP_EOL, $design->features ?? [])) }}</textarea>
                <small>หนึ่งบรรทัดจะแสดงเป็นหนึ่งรายการบนหน้า Detail</small>
            </div>
        </div>
    </section>

    <section class="card panel house-form-section">
        <header>
            <span>04</span>
            <div><h2>รูปภาพ</h2><p>รูปปกใช้บนหน้ารวม ส่วนแกลเลอรีใช้ในหน้า Detail</p></div>
        </header>
        <div class="house-media-editor">
            <div class="field">
                <label for="cover">{{ $isEdit ? 'เปลี่ยนรูปปก' : 'รูปปก' }}</label>
                <div class="house-cover-preview" data-house-cover-preview>
                    @if ($design->previewCoverUrl())
                        <img src="{{ $design->previewCoverUrl() }}" alt="{{ $design->cover_alt }}">
                    @else
                        <span>เลือกภาพเพื่อดูตัวอย่าง</span>
                    @endif
                </div>
                <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" {{ $isEdit ? '' : 'required' }} data-house-cover-input>
                <small>แนะนำภาพแนวนอน JPG, PNG หรือ WebP ไม่เกิน 12MB ระบบจะย่อและแปลงเป็น WebP อัตโนมัติ</small>
            </div>
            <div class="field">
                <label for="cover_alt">คำอธิบายรูปปกสำหรับ SEO</label>
                <input id="cover_alt" name="cover_alt" type="text" maxlength="255" value="{{ old('cover_alt', $design->cover_alt) }}" required>
                <label for="gallery" style="margin-top: 12px;">เพิ่มรูปแกลเลอรี</label>
                <input id="gallery" name="gallery[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                <small>เพิ่มได้ครั้งละไม่เกิน 12 รูป ระบบจะย่อและแปลงเป็น WebP ก่อนจัดเก็บ</small>
            </div>
        </div>

        @if ($isEdit && $design->images->isNotEmpty())
            <div class="house-gallery-editor">
                @foreach ($design->images as $image)
                    <article>
                        <img src="{{ $image->previewUrl() }}" alt="{{ $image->alt_text }}">
                        <div class="field">
                            <label for="gallery-alt-{{ $image->id }}">Alt text</label>
                            <input id="gallery-alt-{{ $image->id }}" name="gallery_existing[{{ $image->id }}][alt_text]" type="text" maxlength="255" value="{{ old("gallery_existing.{$image->id}.alt_text", $image->alt_text) }}" required>
                        </div>
                        <div class="field">
                            <label for="gallery-caption-{{ $image->id }}">คำบรรยาย</label>
                            <input id="gallery-caption-{{ $image->id }}" name="gallery_existing[{{ $image->id }}][caption]" type="text" maxlength="255" value="{{ old("gallery_existing.{$image->id}.caption", $image->caption) }}">
                        </div>
                        <div class="field">
                            <label for="gallery-order-{{ $image->id }}">ลำดับ</label>
                            <input id="gallery-order-{{ $image->id }}" name="gallery_existing[{{ $image->id }}][sort_order]" type="number" min="0" max="9999" value="{{ old("gallery_existing.{$image->id}.sort_order", $image->sort_order) }}" required>
                        </div>
                        <button class="house-gallery-delete" type="submit" form="delete-gallery-{{ $image->id }}">ลบรูปนี้</button>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card panel house-form-section">
        <header>
            <span>05</span>
            <div><h2>SEO</h2><p>ข้อมูลสำหรับผลค้นหาและการแชร์ลิงก์</p></div>
        </header>
        <div class="form-grid">
            <div class="field full"><label for="seo_title">SEO Title</label><input id="seo_title" name="seo_title" type="text" maxlength="255" value="{{ old('seo_title', $design->seo_title) }}"></div>
            <div class="field full"><label for="seo_description">SEO Description</label><textarea id="seo_description" name="seo_description" rows="4" maxlength="500">{{ old('seo_description', $design->seo_description) }}</textarea></div>
        </div>
    </section>

    <div class="house-form-actions">
        <button class="button" type="submit">{{ $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มแบบบ้าน' }}</button>
        <a class="button secondary" href="{{ route('admin.house-designs.index') }}">ยกเลิก</a>
    </div>
</form>

@if ($isEdit)
    @foreach ($design->images as $image)
        <form id="delete-gallery-{{ $image->id }}" method="POST" action="{{ route('admin.house-designs.gallery.destroy', [$design, $image]) }}" onsubmit="return confirm('ลบรูปนี้ออกจากแกลเลอรีใช่ไหม?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

<script>
    (() => {
        const input = document.querySelector('[data-house-cover-input]');
        const preview = document.querySelector('[data-house-cover-preview]');
        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (! file || ! preview) return;
            const url = URL.createObjectURL(file);
            preview.innerHTML = '';
            const image = document.createElement('img');
            image.src = url;
            image.alt = 'ตัวอย่างรูปปกที่เลือก';
            image.onload = () => URL.revokeObjectURL(url);
            preview.appendChild(image);
        });
    })();
</script>
