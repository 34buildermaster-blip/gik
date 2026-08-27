<form class="card panel project-form" method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}">
    @csrf
    @if ($project->exists) @method('PUT') @endif

    <div class="project-form-section">
        <div class="form-section-heading"><span>01</span><div><h2>ข้อมูลโครงการ</h2><p>รายละเอียดหลักที่ใช้ระบุโครงการ</p></div></div>
        <div class="form-grid">
            <div class="field"><label for="code">รหัสโครงการ</label><input id="code" name="code" value="{{ old('code', $project->code) }}" placeholder="เช่น BMC-2026-001" required></div>
            <div class="field"><label for="name">ชื่อโครงการ</label><input id="name" name="name" value="{{ old('name', $project->name) }}" required></div>
            <div class="field"><label for="type">ประเภทงาน</label><select id="type" name="type" required>@foreach ($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('type', $project->type) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="manager_id">ผู้ดูแลโครงการ</label><select id="manager_id" name="manager_id"><option value="">ยังไม่กำหนด</option>@foreach ($managers as $manager)<option value="{{ $manager->id }}" @selected((string) old('manager_id', $project->manager_id) === (string) $manager->id)>{{ $manager->name }}{{ $manager->role === 'inspector' ? ' · ผู้ตรวจหน้างาน' : ' · Admin' }}</option>@endforeach</select></div>
            <div class="field full"><label for="address">ที่อยู่หน้างาน</label><textarea id="address" name="address" rows="3">{{ old('address', $project->address) }}</textarea></div>
            <div class="field full"><label for="summary">คำอธิบายโครงการ</label><textarea id="summary" name="summary" rows="4">{{ old('summary', $project->summary) }}</textarea></div>
        </div>
    </div>

    <div class="project-form-section">
        <div class="form-section-heading"><span>02</span><div><h2>ระยะเวลาและสถานะ</h2><p>ใช้แสดงภาพรวมความคืบหน้าให้ลูกค้า</p></div></div>
        <div class="form-grid">
            <div class="field"><label for="start_date">วันที่เริ่มงาน</label><input id="start_date" name="start_date" type="date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"></div>
            <div class="field"><label for="estimated_end_date">กำหนดส่งโดยประมาณ</label><input id="estimated_end_date" name="estimated_end_date" type="date" value="{{ old('estimated_end_date', $project->estimated_end_date?->format('Y-m-d')) }}"></div>
            <div class="field"><label for="status">สถานะ</label><select id="status" name="status" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="progress_percent">ความคืบหน้ารวม (%)</label><input id="progress_percent" name="progress_percent" type="number" min="0" max="100" value="{{ old('progress_percent', $project->progress_percent ?? 0) }}" required @disabled(($project->steps_count ?? 0) > 0)>@if(($project->steps_count ?? 0) > 0)<input type="hidden" name="progress_percent" value="{{ $project->progress_percent }}"><small>คำนวณอัตโนมัติจากขั้นตอนงาน</small>@endif</div>
        </div>
    </div>

    <div class="project-form-section">
        <div class="form-section-heading"><span>03</span><div><h2>ลูกค้าที่ติดตามโครงการ</h2><p>ลูกค้าที่เลือกเท่านั้นจึงจะเปิดดู Timeline และรูปหน้างานได้</p></div></div>
        <div class="customer-check-grid">
            @forelse ($customers as $customer)
                <label class="customer-check">
                    <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" @checked(in_array($customer->id, old('customer_ids', $selectedCustomers), true))>
                    <span class="user-list-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                    <span><strong>{{ $customer->name }}</strong><small>{{ $customer->email }}</small></span>
                </label>
            @empty
                <p class="muted">ยังไม่มีบัญชี User กรุณาสร้างสมาชิกก่อนสร้างโครงการ</p>
            @endforelse
        </div>
    </div>

    <div class="project-form-actions">
        <button class="button" type="submit">{{ $project->exists ? 'บันทึกการแก้ไข' : 'สร้างโครงการ' }}</button>
        <a class="button secondary" href="{{ route('admin.projects.index') }}">ยกเลิก</a>
    </div>
</form>
