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
            <small>ทุกบัญชีในระบบ</small>
        </article>
        <article class="card user-stat-card">
            <span>ผู้ดูแลระบบ</span>
            <strong>{{ $adminCount }}</strong>
            <small>เข้าถึงแดชบอร์ดและบทความ</small>
        </article>
        <article class="card user-stat-card">
            <span>ผู้ตรวจหน้างาน</span>
            <strong>{{ $inspectorCount }}</strong>
            <small>อัปเดตเฉพาะโครงการที่ได้รับมอบหมาย</small>
        </article>
        <article class="card user-stat-card">
            <span>ลูกค้า</span>
            <strong>{{ $memberCount }}</strong>
            <small>ติดตามข้อมูลโครงการของตนเอง</small>
        </article>
    </section>

    <section class="card panel user-list-panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองบทบาทผู้ใช้งาน">
                <a class="filter-chip {{ ! array_key_exists($role, $roleLabels) ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search])) }}">ทั้งหมด</a>
                <a class="filter-chip {{ $role === 'admin' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'admin'])) }}">Admin</a>
                <a class="filter-chip {{ $role === 'inspector' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'inspector'])) }}">ผู้ตรวจหน้างาน</a>
                <a class="filter-chip {{ $role === 'user' ? 'is-active' : '' }}" href="{{ route('admin.users.index', array_filter(['q' => $search, 'role' => 'user'])) }}">ลูกค้า</a>
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
                                <div class="user-row-actions">
                                <form class="role-form" method="POST" action="{{ route('admin.users.role', $listedUser) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" aria-label="สิทธิ์ของ {{ $listedUser->name }}" @disabled(auth()->id() === $listedUser->id)>
                                        <option value="user" @selected($listedUser->role === 'user')>User</option>
                                        <option value="inspector" @selected($listedUser->role === 'inspector')>ผู้ตรวจหน้างาน</option>
                                        <option value="admin" @selected($listedUser->role === 'admin')>Admin</option>
                                    </select>
                                    <button class="icon-save-button" type="submit" title="บันทึกสิทธิ์" aria-label="บันทึกสิทธิ์ของ {{ $listedUser->name }}" @disabled(auth()->id() === $listedUser->id)>
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg>
                                    </button>
                                </form>
                                @if($listedUser->role === 'user')
                                <a class="icon-save-button" href="{{ route('admin.customers.show', $listedUser) }}" title="ข้อมูลลูกค้าของ {{ $listedUser->name }}" aria-label="เปิดข้อมูลลูกค้าของ {{ $listedUser->name }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path><path d="M17 4h4"></path><path d="M19 2v4"></path></svg>
                                </a>
                                @endif
                                <a class="icon-save-button" href="{{ route('admin.users.security.show', $listedUser) }}" title="ความปลอดภัยของ {{ $listedUser->name }}" aria-label="จัดการความปลอดภัยของ {{ $listedUser->name }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-3Z"></path><path d="M9 12h6"></path></svg>
                                </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><p class="muted user-empty">ไม่พบบัญชีที่ตรงกับเงื่อนไข</p></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $users->links() }}</div>
    </section>
</x-admin-layout>
