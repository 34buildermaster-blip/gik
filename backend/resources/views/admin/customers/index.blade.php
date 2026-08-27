<x-admin-layout title="ข้อมูลลูกค้า | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">CUSTOMER 360</p>
            <h1>ข้อมูลลูกค้า</h1>
            <p class="muted" style="margin:7px 0 0;">รวมข้อมูลติดต่อ บัญชี และภาพรวมโครงการของลูกค้าไว้ในพื้นที่เดียว</p>
        </div>
        <div class="customer-heading-actions">
            <a class="button secondary" href="{{ route('admin.users.index', ['role' => 'user']) }}">จัดการบัญชีลูกค้า</a>
            <a class="button" href="{{ route('admin.users.create', ['role' => 'user']) }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                เพิ่มลูกค้า
            </a>
        </div>
    </div>

    <section class="customer-360-stats" aria-label="สรุปลูกค้า">
        <article class="card customer-stat is-primary"><span>ลูกค้าทั้งหมด</span><strong>{{ $totalCustomers }}</strong><small>บัญชีลูกค้าในระบบ</small></article>
        <article class="card customer-stat"><span>ใช้งานอยู่</span><strong>{{ $activeCustomers }}</strong><small>ติดตามหรือมีโครงการอยู่</small></article>
        <article class="card customer-stat"><span>ผู้สนใจ</span><strong>{{ $prospectCustomers }}</strong><small>รอเริ่มต้นโครงการ</small></article>
        <article class="card customer-stat"><span>พักการใช้งาน</span><strong>{{ $inactiveCustomers }}</strong><small>ไม่มีการดำเนินงานปัจจุบัน</small></article>
    </section>

    <section class="card panel customer-list-panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองสถานะลูกค้า">
                <a class="filter-chip {{ ! array_key_exists($status, $statusLabels) ? 'is-active' : '' }}" href="{{ route('admin.customers.index', array_filter(['q' => $search])) }}">ทั้งหมด</a>
                @foreach($statusLabels as $value => $label)
                    <a class="filter-chip {{ $status === $value ? 'is-active' : '' }}" href="{{ route('admin.customers.index', array_filter(['q' => $search, 'status' => $value])) }}">{{ $label }}</a>
                @endforeach
            </div>
            <p class="result-note">@if($search !== '') ผลการค้นหา “{{ $search }}” · @endif {{ $customers->total() }} ลูกค้า</p>
        </div>

        <div class="customer-card-list">
            @forelse($customers as $customer)
                @php($latestProject = $customer->projects->sortByDesc('updated_at')->first())
                <a class="customer-list-card" href="{{ route('admin.customers.show', $customer) }}">
                    <span class="customer-list-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                    <span class="customer-list-identity">
                        <span class="customer-status status-{{ $customer->customer_status }}">{{ $statusLabels[$customer->customer_status] ?? $customer->customer_status }}</span>
                        <strong>{{ $customer->name }}</strong>
                        <small>{{ $customer->email }}</small>
                        <small>{{ $customer->phone ?: 'ยังไม่ได้ระบุเบอร์โทร' }}</small>
                    </span>
                    <span class="customer-list-project">
                        <small>โครงการทั้งหมด</small>
                        <strong>{{ $customer->projects_count }}</strong>
                        <span>{{ $latestProject ? $latestProject->name.' · '.$latestProject->progress_percent.'%' : 'ยังไม่มีโครงการ' }}</span>
                    </span>
                    <span class="customer-open-icon" aria-hidden="true">›</span>
                </a>
            @empty
                <div class="customer-empty-state"><h2>ไม่พบข้อมูลลูกค้า</h2><p>ลองเปลี่ยนคำค้นหาหรือตัวกรองสถานะ</p></div>
            @endforelse
        </div>

        <div class="pagination">{{ $customers->links() }}</div>
    </section>
</x-admin-layout>
