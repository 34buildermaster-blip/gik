<form class="card panel form home-slide-form" method="POST" enctype="multipart/form-data" action="{{ $slide->exists ? route('admin.home-slides.update', $slide) : route('admin.home-slides.store') }}">
    @csrf
    @if ($slide->exists)
        @method('PUT')
    @endif

    <div class="home-slide-editor">
        <div class="home-slide-fields">
            <div class="form-grid">
                @if (! $slide->exists)
                    <div class="field">
                        <label for="section">ตำแหน่งที่แสดง</label>
                        <select id="section" name="section" required data-slide-section>
                            @foreach (\App\Models\HomeSlide::SECTION_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('section', $slide->section) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="section" value="{{ $slide->section }}">
                    <div class="field">
                        <label>ตำแหน่งที่แสดง</label>
                        <div class="home-slide-readonly">{{ \App\Models\HomeSlide::SECTION_LABELS[$slide->section] }}</div>
                    </div>
                @endif

                <div class="field">
                    <label for="sort_order">ลำดับการแสดง</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="9999" step="1" value="{{ old('sort_order', $slide->sort_order) }}" required>
                    <small>เลขน้อยจะแสดงก่อน เช่น 10, 20, 30</small>
                </div>

                <div class="field full">
                    <label for="title">หัวข้อหลัก</label>
                    <input id="title" name="title" type="text" maxlength="160" value="{{ old('title', $slide->title) }}" required>
                </div>

                <div class="field full" data-hero-field>
                    <label for="title_line_2">หัวข้อบรรทัดที่สอง</label>
                    <input id="title_line_2" name="title_line_2" type="text" maxlength="160" value="{{ old('title_line_2', $slide->title_line_2) }}">
                </div>

                <div class="field" data-hero-field>
                    <label for="eyebrow">ข้อความเล็กเหนือหัวข้อ</label>
                    <input id="eyebrow" name="eyebrow" type="text" maxlength="120" value="{{ old('eyebrow', $slide->eyebrow) }}" placeholder="DESIGN · BUILD · RENOVATE">
                </div>

                <div class="field" data-hero-field>
                    <label for="label">ป้ายกำกับด้านล่าง</label>
                    <input id="label" name="label" type="text" maxlength="120" value="{{ old('label', $slide->label) }}" placeholder="BUILD WITH CLARITY">
                </div>

                <div class="field full" data-hero-field>
                    <label for="description">คำอธิบาย</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000">{{ old('description', $slide->description) }}</textarea>
                </div>

                <div class="field full">
                    <label for="alt_text">คำอธิบายรูปสำหรับ SEO และผู้ใช้โปรแกรมอ่านหน้าจอ</label>
                    <input id="alt_text" name="alt_text" type="text" maxlength="255" value="{{ old('alt_text', $slide->alt_text) }}" required>
                </div>

                <label class="settings-toggle full">
                    <span><strong>เปิดแสดงบนหน้าแรก</strong><small>ปิดได้ชั่วคราวโดยไม่ต้องลบข้อมูล</small></span>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->is_active))>
                    <i></i>
                </label>
            </div>
        </div>

        <aside class="home-slide-upload">
            <div class="home-slide-current">
                @if ($slide->previewUrl())
                    <img src="{{ $slide->previewUrl() }}" alt="{{ $slide->alt_text }}">
                @else
                    <span>เลือกภาพเพื่อดูตัวอย่าง</span>
                @endif
            </div>
            <div class="field">
                <label for="image">{{ $slide->exists ? 'เปลี่ยนรูปภาพ' : 'รูปภาพ' }}</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" {{ $slide->exists ? '' : 'required' }} data-slide-image>
                <small>แนะนำภาพแนวนอน JPG, PNG หรือ WebP ไม่เกิน 12MB และใช้ภาพขนาดใกล้เคียงกันในแต่ละกลุ่ม</small>
            </div>
        </aside>
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ $slide->exists ? 'บันทึกการแก้ไข' : 'เพิ่มสไลด์' }}</button>
        <a class="button secondary" href="{{ route('admin.home-slides.index') }}">ยกเลิก</a>
    </div>
</form>

<script>
    (() => {
        const sectionInput = document.querySelector('[data-slide-section]');
        const heroFields = document.querySelectorAll('[data-hero-field]');
        const imageInput = document.querySelector('[data-slide-image]');
        const preview = document.querySelector('.home-slide-current');

        const syncSection = () => {
            const section = sectionInput?.value || @json($slide->section);
            heroFields.forEach((field) => {
                field.hidden = section !== 'hero';
            });
        };

        sectionInput?.addEventListener('change', syncSection);
        imageInput?.addEventListener('change', () => {
            const file = imageInput.files?.[0];
            if (!file || !preview) return;
            const url = URL.createObjectURL(file);
            preview.innerHTML = '';
            const image = document.createElement('img');
            image.src = url;
            image.alt = 'ตัวอย่างรูปที่เลือก';
            image.onload = () => URL.revokeObjectURL(url);
            preview.appendChild(image);
        });
        syncSection();
    })();
</script>
