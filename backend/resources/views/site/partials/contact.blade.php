<section class="contact-band section" id="contact">
    <div class="contact-visual reveal"><img src="{{ url('/site-contact/tropical-japandi-contact.webp') }}" alt="บ้านสมัยใหม่สำหรับเริ่มต้นปรึกษาโครงการ"><div><p class="eyebrow">START YOUR PROJECT</p><h2>เริ่มต้นจากการคุยกัน<br>อย่างเข้าใจ</h2></div></div>
    <form class="contact-form reveal" data-contact-form>
        <p class="eyebrow">CONTACT US</p><h2>{{ $siteSettings['cta']['contact_heading'] }}</h2><p>{{ $siteSettings['cta']['contact_description'] }}</p>
        <label>ชื่อ-นามสกุล<input name="name" minlength="2" maxlength="120" placeholder="ชื่อของคุณ" required></label>
        <div class="form-row"><label>เบอร์โทรศัพท์<input name="phone" type="tel" minlength="8" maxlength="30" placeholder="08X-XXX-XXXX" required></label><label>ประเภทงาน<select name="service_type"><option value="">เลือกประเภทงาน</option><option>ออกแบบบ้าน</option><option>สร้างบ้าน</option><option>รีโนเวท</option><option>บิวท์อิน</option></select></label></div>
        <label>อีเมล (ไม่บังคับ)<input name="email" type="email" maxlength="255" placeholder="อีเมลสำหรับรับข้อมูลเพิ่มเติม"></label>
        <label>รายละเอียด<textarea name="message" rows="4" maxlength="5000" placeholder="พื้นที่ งบประมาณ หรือสิ่งที่ต้องการปรึกษา"></textarea></label>
        <input class="honeypot" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <p class="form-feedback" data-form-feedback role="status"></p>
        <button class="button" type="submit">ส่งข้อมูลให้ทีมงาน <span>→</span></button>
    </form>
</section>
