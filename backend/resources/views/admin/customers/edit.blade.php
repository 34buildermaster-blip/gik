<x-admin-layout title="แก้ไขข้อมูล {{ $customer->name }} | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">EDIT CUSTOMER</p>
            <h1>แก้ไขข้อมูลลูกค้า</h1>
            <p class="muted" style="margin:7px 0 0;">ข้อมูลส่วนนี้ใช้สำหรับการดูแลลูกค้าและออกเอกสารภายในระบบ</p>
        </div>
        <a class="button secondary" href="{{ route('admin.customers.show', $customer) }}">กลับหน้ารายละเอียด</a>
    </div>

    <form class="card panel customer-edit-form" method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf
        @method('PUT')

        <section class="customer-form-section">
            <div class="form-section-heading"><span>01</span><div><h2>ข้อมูลหลักและการติดต่อ</h2><p>ข้อมูลที่ทีมใช้ติดต่อและระบุตัวลูกค้า</p></div></div>
            <div class="customer-form-grid">
                <div class="field"><label for="name">ชื่อลูกค้า</label><input id="name" name="name" value="{{ old('name', $customer->name) }}" required></div>
                <div class="field"><label for="email">อีเมล</label><input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required></div>
                <div class="field"><label for="phone">เบอร์โทรศัพท์</label><input id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" inputmode="tel"></div>
                <div class="field"><label for="preferred_contact_channel">ช่องทางที่สะดวก</label><select id="preferred_contact_channel" name="preferred_contact_channel" required>@foreach($contactChannelLabels as $value => $label)<option value="{{ $value }}" @selected(old('preferred_contact_channel', $customer->preferred_contact_channel) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field full"><label for="line_recipient_id">LINE Recipient ID</label><input id="line_recipient_id" name="line_recipient_id" value="{{ old('line_recipient_id', $customer->line_recipient_id) }}"><small>ใช้สำหรับแจ้งเตือนผ่าน LINE Messaging API ไม่ใช่ชื่อ LINE</small></div>
                <div class="field full"><label for="address">ที่อยู่</label><textarea id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea></div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="form-section-heading"><span>02</span><div><h2>ข้อมูลออกเอกสาร</h2><p>เลขประจำตัวผู้เสียภาษีจะถูกเข้ารหัสก่อนบันทึก</p></div></div>
            <div class="customer-form-grid">
                <div class="field"><label for="billing_name">ชื่อบุคคล/บริษัทสำหรับออกเอกสาร</label><input id="billing_name" name="billing_name" value="{{ old('billing_name', $customer->billing_name) }}"></div>
                <div class="field"><label for="tax_id">เลขประจำตัวผู้เสียภาษี</label><input id="tax_id" name="tax_id" value="{{ old('tax_id', $customer->tax_id) }}" autocomplete="off"></div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="form-section-heading"><span>03</span><div><h2>ผู้ติดต่อสำรอง</h2><p>ใช้เมื่อไม่สามารถติดต่อลูกค้าผ่านช่องทางหลักได้</p></div></div>
            <div class="customer-form-grid">
                <div class="field"><label for="emergency_contact_name">ชื่อผู้ติดต่อสำรอง</label><input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}"></div>
                <div class="field"><label for="emergency_contact_phone">เบอร์ผู้ติดต่อสำรอง</label><input id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}" inputmode="tel"></div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="form-section-heading"><span>04</span><div><h2>สถานะและหมายเหตุภายใน</h2><p>หมายเหตุส่วนนี้แสดงเฉพาะ Admin</p></div></div>
            <div class="customer-form-grid">
                <div class="field"><label for="customer_status">สถานะลูกค้า</label><select id="customer_status" name="customer_status" required>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('customer_status', $customer->customer_status) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field full"><label for="internal_notes">หมายเหตุภายใน</label><textarea id="internal_notes" name="internal_notes" rows="5">{{ old('internal_notes', $customer->internal_notes) }}</textarea></div>
            </div>
        </section>

        <div class="customer-form-actions"><a class="button secondary" href="{{ route('admin.customers.show', $customer) }}">ยกเลิก</a><button class="button" type="submit">บันทึกข้อมูลลูกค้า</button></div>
    </form>
</x-admin-layout>
