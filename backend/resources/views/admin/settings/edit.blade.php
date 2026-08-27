<x-admin-layout title="ตั้งค่าเว็บไซต์ | 34 Build Master Admin">
    @php
        $textFields = [
            'company_name_th' => ['ชื่อบริษัทภาษาไทย', 'ชื่อที่ใช้ในเอกสารและส่วนติดต่อ'],
            'company_name_en' => ['ชื่อบริษัทภาษาอังกฤษ', 'ชื่อที่แสดงบน Header และ Footer'],
            'tagline' => ['คำโปรยแบรนด์', 'สรุปบริการหลักในประโยคเดียว'],
            'phone_display' => ['เบอร์โทรที่แสดง', 'เช่น 081-9512-297'],
            'phone_href' => ['ลิงก์โทรศัพท์', 'ต้องขึ้นต้นด้วย tel: เช่น tel:+66819512297'],
            'email' => ['อีเมลติดต่อ', 'ใช้รับข้อมูลจากฟอร์มหน้าเว็บไซต์'],
            'service_area' => ['พื้นที่ให้บริการ', 'เช่น เชียงใหม่ และพื้นที่ใกล้เคียง'],
            'business_hours' => ['เวลาทำการ', 'ระบุวันและเวลาที่ติดต่อได้'],
            'copyright' => ['ข้อความลิขสิทธิ์', 'แสดงบรรทัดล่างสุดของ Footer'],
        ];
        $socialFields = [
            'facebook_url' => ['Facebook', 'https://www.facebook.com/...'],
            'instagram_url' => ['Instagram', 'https://www.instagram.com/...'],
            'line_url' => ['LINE OA', 'https://line.me/...'],
            'tiktok_url' => ['TikTok', 'https://www.tiktok.com/...'],
        ];
        $imageFields = [
            'logo' => ['โลโก้หลัก', 'logo_url', 'ใช้ใน Header และจุดแสดงแบรนด์'],
            'footer_logo' => ['โลโก้ Footer', 'footer_logo_url', 'เว้นว่างเพื่อใช้โลโก้หลัก'],
            'favicon' => ['ไอคอนเว็บไซต์', 'favicon_url', 'ใช้บนแท็บเบราว์เซอร์'],
            'og_image' => ['ภาพแชร์โซเชียล', 'og_image_url', 'แนะนำขนาด 1200 × 630 พิกเซล'],
        ];
        $visibilityFields = [
            'navigation' => [
                'show_house_designs' => ['เมนูแบบบ้าน', 'แสดงลิงก์แบบบ้านในเมนูหลัก'],
                'show_updates' => ['เมนูอัปเดตงาน', 'แสดงหน้ารวมความคืบหน้าโครงการ'],
                'show_blog' => ['เมนูบทความ', 'แสดงลิงก์บทความในเมนูหลัก'],
                'show_faq' => ['เมนู FAQ', 'แสดงลิงก์คำถามที่พบบ่อย'],
            ],
            'display' => [
                'show_home_services' => ['บริการของเรา', 'แสดง Section บริการบนหน้าแรก'],
                'show_home_projects' => ['ผลงานเด่น', 'แสดง Section ผลงานบนหน้าแรก'],
                'show_home_process' => ['ขั้นตอนทำงาน', 'แสดง Process บนหน้าแรก'],
                'show_home_partners' => ['แบรนด์วัสดุ', 'แสดง Material Partners บนหน้าแรก'],
                'show_home_reviews' => ['รีวิวลูกค้า', 'แสดง Client Experience บนหน้าแรก'],
                'show_home_contact' => ['แบบฟอร์มติดต่อ', 'แสดง Contact Section บนหน้าแรก'],
            ],
        ];
    @endphp

    <div class="topbar settings-heading">
        <div>
            <p class="eyebrow">WEBSITE SETTINGS</p>
            <h1>ตั้งค่าเว็บไซต์</h1>
            <p class="muted">แก้ไขข้อมูลส่วนกลาง ช่องทางติดต่อ และส่วนที่ต้องการแสดงบนหน้าบ้าน</p>
        </div>
        <a class="button secondary" href="{{ config('app.frontend_url') }}" target="_blank" rel="noreferrer">ดูหน้าบ้าน</a>
    </div>

    <form class="settings-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="settings-layout">
            <nav class="card settings-index" aria-label="หมวดการตั้งค่า">
                <strong>หมวดการตั้งค่า</strong>
                <a href="#general">ข้อมูลบริษัท</a>
                <a href="#branding">แบรนด์และโลโก้</a>
                <a href="#social">โซเชียลมีเดีย</a>
                <a href="#cta">ข้อความเชิญชวน</a>
                <a href="#visibility">เมนูและ Section</a>
                <a href="#seo">SEO พื้นฐาน</a>
            </nav>

            <div class="settings-sections">
                <section class="card settings-card" id="general">
                    <div class="settings-card-heading">
                        <span>01</span><div><h2>ข้อมูลบริษัทและการติดต่อ</h2><p>ข้อมูลชุดนี้ใช้ร่วมกันใน Header, Footer และปุ่มติดต่อทั่วทั้งเว็บไซต์</p></div>
                    </div>
                    <div class="settings-grid">
                        @foreach ($textFields as $key => [$label, $help])
                            <label class="field {{ in_array($key, ['tagline', 'copyright'], true) ? 'settings-full' : '' }}">
                                <span>{{ $label }}</span>
                                <input name="{{ $key }}" type="{{ $key === 'email' ? 'email' : 'text' }}" value="{{ old($key, $settings['general'][$key]) }}" required>
                                <small>{{ $help }}</small>
                            </label>
                        @endforeach
                        <label class="field settings-full">
                            <span>ที่อยู่บริษัท</span>
                            <textarea name="address" rows="3" required>{{ old('address', $settings['general']['address']) }}</textarea>
                            <small>ใช้แสดงใน Footer และข้อมูลธุรกิจสำหรับ Search Engine</small>
                        </label>
                    </div>
                </section>

                <section class="card settings-card" id="branding">
                    <div class="settings-card-heading">
                        <span>02</span><div><h2>แบรนด์และรูปภาพหลัก</h2><p>รองรับ JPG, PNG หรือ WebP ระบบจะเก็บไฟล์เดิมไว้จนกว่าจะอัปโหลดไฟล์ใหม่</p></div>
                    </div>
                    <div class="settings-upload-grid">
                        @foreach ($imageFields as $input => [$label, $urlKey, $help])
                            @php $group = $input === 'og_image' ? 'seo' : 'branding'; @endphp
                            <label class="settings-upload">
                                <span class="settings-upload-preview">
                                    @if ($settings[$group][$urlKey])
                                        <img src="{{ $settings[$group][$urlKey] }}" alt="{{ $label }} ปัจจุบัน">
                                    @else
                                        <b>{{ $input === 'favicon' ? '34' : 'ยังไม่มีรูป' }}</b>
                                    @endif
                                </span>
                                <strong>{{ $label }}</strong>
                                <small>{{ $help }}</small>
                                <input name="{{ $input }}" type="file" accept="{{ $input === 'favicon' ? 'image/*,.ico' : 'image/jpeg,image/png,image/webp' }}">
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="card settings-card" id="social">
                    <div class="settings-card-heading">
                        <span>03</span><div><h2>ช่องทางโซเชียลมีเดีย</h2><p>เว้นช่องว่างได้หากยังไม่ต้องการแสดงช่องทางนั้น</p></div>
                    </div>
                    <div class="settings-grid">
                        @foreach ($socialFields as $key => [$label, $placeholder])
                            <label class="field"><span>{{ $label }}</span><input name="{{ $key }}" type="url" value="{{ old($key, $settings['social'][$key]) }}" placeholder="{{ $placeholder }}"></label>
                        @endforeach
                    </div>
                </section>

                <section class="card settings-card" id="cta">
                    <div class="settings-card-heading">
                        <span>04</span><div><h2>ข้อความปุ่มและ Contact Section</h2><p>ควรใช้ข้อความสั้น ชัดเจน และบอกสิ่งที่ลูกค้าจะได้รับ</p></div>
                    </div>
                    <div class="settings-grid">
                        <label class="field"><span>ข้อความปุ่มปรึกษา</span><input name="consultation_label" value="{{ old('consultation_label', $settings['cta']['consultation_label']) }}" required></label>
                        <label class="field"><span>ข้อความปุ่มติดตามงาน</span><input name="tracking_label" value="{{ old('tracking_label', $settings['cta']['tracking_label']) }}" required></label>
                        <label class="field settings-full"><span>หัวข้อ Contact Section</span><input name="contact_heading" value="{{ old('contact_heading', $settings['cta']['contact_heading']) }}" required></label>
                        <label class="field settings-full"><span>คำอธิบาย Contact Section</span><textarea name="contact_description" rows="3" required>{{ old('contact_description', $settings['cta']['contact_description']) }}</textarea></label>
                    </div>
                </section>

                <section class="card settings-card" id="visibility">
                    <div class="settings-card-heading">
                        <span>05</span><div><h2>เมนูและ Section ที่แสดง</h2><p>ปิดส่วนที่ยังไม่พร้อมเผยแพร่ได้ทันที โดยข้อมูลเดิมจะไม่ถูกลบ</p></div>
                    </div>
                    @foreach ($visibilityFields as $group => $fields)
                        <h3 class="settings-subheading">{{ $group === 'navigation' ? 'เมนูหลัก' : 'Section หน้าแรก' }}</h3>
                        <div class="settings-toggle-grid">
                            @foreach ($fields as $key => [$label, $help])
                                <label class="settings-toggle">
                                    <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                    <input name="{{ $key }}" type="checkbox" value="1" @checked(old($key, $settings[$group][$key]))>
                                    <i aria-hidden="true"></i>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </section>

                <section class="card settings-card" id="seo">
                    <div class="settings-card-heading">
                        <span>06</span><div><h2>SEO พื้นฐาน</h2><p>กำหนดชื่อและคำอธิบายสำรองสำหรับหน้าเว็บไซต์และการแชร์ลิงก์</p></div>
                    </div>
                    <div class="settings-grid">
                        <label class="field settings-full"><span>ชื่อเว็บไซต์ (Title)</span><input name="default_title" value="{{ old('default_title', $settings['seo']['default_title']) }}" maxlength="70" required><small>แนะนำไม่เกิน 60–70 ตัวอักษร</small></label>
                        <label class="field settings-full"><span>คำอธิบายเว็บไซต์</span><textarea name="default_description" rows="3" maxlength="180" required>{{ old('default_description', $settings['seo']['default_description']) }}</textarea><small>แนะนำประมาณ 140–160 ตัวอักษร</small></label>
                    </div>
                </section>
            </div>
        </div>

        <div class="settings-savebar">
            <span>ตรวจสอบข้อมูลให้ครบก่อนบันทึก การเปลี่ยนแปลงจะแสดงบนหน้าบ้านหลังโหลดหน้าใหม่</span>
            <button class="button" type="submit">บันทึกการตั้งค่า</button>
        </div>
    </form>
</x-admin-layout>
