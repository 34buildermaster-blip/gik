<x-admin-layout title="จัดการผู้ใช้งาน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">USER MANAGEMENT</p>
            <h1>จัดการผู้ใช้งาน</h1>
            <p class="muted" style="margin: 7px 0 0;">ตรวจสอบบัญชีสมาชิกและกำหนดสิทธิ์การเข้าถึงระบบ</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            เพิ่มผู้ใช้งาน
        </a>
    </div>

    <section class="user-stats" aria-label="สรุปผู้ใช้งาน">
        <article class="card user-stat-card is-primary">
            <span>ผู้ใช้งานทั้งหมด</span>
            <strong>{{ $totalUsers }}</strong>
            <small>ระงับอยู่ {{ $suspendedCount }} บัญชี</small>
        </article>
        <article class="card user-stat-card">
            <span>ผู้ดูแลระบบ</span>
            <strong>{{ $adminCount }}</strong>
            <small>บัญชีที่พร้อมใช้งาน</small>
        </article>
        <article class="card user-stat-card">
            <span>ผู้ตรวจหน้างาน</span>
            <strong>{{ $inspectorCount }}</strong>
            <small>บัญชีที่พร้อมใช้งาน</small>
        </article>
        <article class="card user-stat-card">
            <span>ลูกค้า</span>
            <strong>{{ $memberCount }}</strong>
            <small>บัญชีที่พร้อมใช้งาน</small>
        </article>
    </section>

    <section class="card panel user-list-panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองบทบาทผู้ใช้งาน">
                <a class="filter-chip {{ ! array_key_exists($role, $roleLabels) ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'status' => $status])) }}">ทุกบทบาท</a>
                <a class="filter-chip {{ $role === 'admin' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'admin', 'status' => $status])) }}">Admin</a>
                <a class="filter-chip {{ $role === 'inspector' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'inspector', 'status' => $status])) }}">ผู้ตรวจหน้างาน</a>
                <a class="filter-chip {{ $role === 'user' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'user', 'status' => $status])) }}">ลูกค้า</a>
            </div>
            <div class="filter-tabs" aria-label="กรองสถานะผู้ใช้งาน">
                <a class="filter-chip {{ ! in_array($status, ['active', 'suspended'], true) ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => $role])) }}">ทุกสถานะ</a>
                <a class="filter-chip {{ $status === 'active' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => $role, 'status' => 'active'])) }}">พร้อมใช้งาน</a>
                <a class="filter-chip {{ $status === 'suspended' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => $role, 'status' => 'suspended'])) }}">ถูกระงับ</a>
            </div>
            <p class="result-note">
                @if ($search !== '') ผลการค้นหา “{{ $search }}” · @endif
                {{ $users->total() }} บัญชี
            </p>
        </div>

        <div class="table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ผู้ใช้งาน</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>วันที่สมัคร</th>
                        <th>สิทธิ์ปัจจุบัน</th>
                        <th>สถานะ</th>
                        <th>จัดการสิทธิ์</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $listedUser)
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <span class="user-list-avatar">{{ mb_strtoupper(mb_substr($listedUser->name, 0, 1)) }}</span>
                                    <span>
                                        <strong>{{ $listedUser->name }}</strong>
                                        <small>{{ $listedUser->email }}</small>
                                    </span>
                                    @if (auth()->id() === $listedUser->id)
                                        <em>บัญชีคุณ</em>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $listedUser->username ?: '-' }}</td>
                            <td>{{ $listedUser->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="role-badge {{ $listedUser->isAdmin() ? 'is-admin' : ($listedUser->isInspector() ? 'is-inspector' : '') }}">
                                    {{ $roleLabels[$listedUser->role] ?? $listedUser->role }}
                                </span>
                            </td>
                            <td>
                                <span class="account-status-badge {{ $listedUser->isDisabled() ? 'is-suspended' : 'is-active' }}">
                                    {{ $listedUser->isDisabled() ? 'ถูกระงับ' : 'พร้อมใช้งาน' }}
                                </span>
                            </td>
                            <td>
                                <div class="user-row-actions">
                                <form class="role-form" method="POST" action="{{ route('admin.users.role', $listedUser) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" aria-label="สิทธิ์ของ {{ $listedUser->name }}" @disabled(auth()->id() === $listedUser->id || $listedUser->isDisabled())>
                                        <option value="user" @selected($listedUser->role === 'user')>User</option>
                                        <option value="inspector" @selected($listedUser->role === 'inspector')>ผู้ตรวจหน้างาน</option>
                                        <option value="admin" @selected($listedUser->role === 'admin')>Admin</option>
                                    </select>
                                    <button class="icon-save-button" type="submit" title="บันทึกสิทธิ์" aria-label="บันทึกสิทธิ์ของ {{ $listedUser->name }}" @disabled(auth()->id() === $listedUser->id || $listedUser->isDisabled())>
                                        <x-ui-icon name="check" />
                                    </button>
                                </form>
                                @if($listedUser->role === 'user')
                                <a class="icon-save-button" href="{{ route('admin.customers.show', $listedUser) }}" title="ข้อมูลลูกค้าของ {{ $listedUser->name }}" aria-label="เปิดข้อมูลลูกค้าของ {{ $listedUser->name }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path><path d="M17 4h4"></path><path d="M19 2v4"></path></svg>
                                </a>
                                @endif
                                <a class="icon-save-button {{ $listedUser->isDisabled() ? 'is-warning' : '' }}" href="{{ route('admin.users.security.show', $listedUser) }}" title="ความปลอดภัยและสถานะของ {{ $listedUser->name }}" aria-label="จัดการความปลอดภัยและสถานะของ {{ $listedUser->name }}">
                                    <x-ui-icon name="shield" />
                                </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><p class="muted user-empty">ไม่พบบัญชีที่ตรงกับเงื่อนไข</p></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $users->links() }}</div>
    </section>
</x-admin-layout>
