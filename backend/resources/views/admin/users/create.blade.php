<x-admin-layout title="เพิ่มผู้ใช้งาน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">NEW USER</p>
            <h1>เพิ่มผู้ใช้งาน</h1>
            <p class="muted" style="margin:7px 0 0;">สร้างบัญชีและกำหนดสิทธิ์เริ่มต้นสำหรับลูกค้าหรือทีมงาน</p>
        </div>
        <a class="button secondary" href="{{ route('admin.users.index') }}">กลับไปจัดการผู้ใช้</a>
    </div>

    <form class="card panel user-create-form" method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="panel-heading">
            <div><p class="eyebrow">ACCOUNT DETAILS</p><h2>ข้อมูลบัญชี</h2><p>ผู้ใช้งานสามารถเปลี่ยนข้อมูลและรหัสผ่านภายหลังได้จากหน้าโปรไฟล์</p></div>
        </div>

        <div class="user-create-grid">
            <div class="field">
                <label for="name">ชื่อที่แสดง</label>
                <input id="name" name="name" value="{{ old('name') }}" placeholder="ชื่อจริงหรือชื่อทีมงาน" required>
            </div>
            <div class="field">
                <label for="username">ชื่อผู้ใช้</label>
                <input id="username" name="username" value="{{ old('username') }}" placeholder="ตัวอักษรอังกฤษ ตัวเลข จุด ขีดกลาง" required>
            </div>
            <div class="field">
                <label for="email">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required>
            </div>
            <div class="field">
                <label for="role">บทบาท</label>
                <select id="role" name="role" required>
                    @foreach($roleLabels as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', 'user') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="password">รหัสผ่านเริ่มต้น</label>
                <input id="password" name="password" type="password" autocomplete="new-password" placeholder="อย่างน้อย 8 ตัวอักษร" required>
            </div>
            <div class="field">
                <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
        </div>

        <div class="user-role-guide">
            <div><strong>ลูกค้า</strong><span>ติดตามเฉพาะโครงการที่ได้รับมอบหมาย</span></div>
            <div><strong>ผู้ตรวจหน้างาน</strong><span>อัปเดตรูป ผลตรวจ และความคืบหน้าของงานที่รับผิดชอบ</span></div>
            <div><strong>ผู้ดูแลระบบ</strong><span>เข้าถึงและจัดการข้อมูลทั้งหมดในระบบ</span></div>
        </div>

        <div class="project-form-actions">
            <button class="button" type="submit">สร้างบัญชีผู้ใช้งาน</button>
            <a class="button secondary" href="{{ route('admin.users.index') }}">ยกเลิก</a>
        </div>
    </form>
</x-admin-layout>
