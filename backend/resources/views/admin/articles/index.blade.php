<x-admin-layout title="บทความ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Articles</p>
            <h1>จัดการบทความ</h1>
            <p class="muted" style="margin: 7px 0 0;">เพิ่ม แก้ไข ตรวจตัวอย่าง และควบคุมการเผยแพร่บทความบนเว็บไซต์</p>
        </div>
        <a class="button" href="{{ route('admin.articles.create') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            เพิ่มบทความ
        </a>
    </div>

    <section class="card panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองสถานะบทความ">
                <a class="filter-chip {{ ! in_array($status, ['draft', 'published'], true) ? 'is-active' : '' }}" href="{{ route('admin.articles.index', array_filter(['q' => $search])) }}">ทั้งหมด</a>
                <a class="filter-chip {{ $status === 'published' ? 'is-active' : '' }}" href="{{ route('admin.articles.index', array_filter(['q' => $search, 'status' => 'published'])) }}">เผยแพร่แล้ว</a>
                <a class="filter-chip {{ $status === 'draft' ? 'is-active' : '' }}" href="{{ route('admin.articles.index', array_filter(['q' => $search, 'status' => 'draft'])) }}">ฉบับร่าง</a>
            </div>
            <p class="result-note">
                @if ($search !== '')
                    ผลการค้นหา “{{ $search }}” ·
                @endif
                {{ $articles->total() }} รายการ
            </p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>รูป</th>
                        <th>บทความ</th>
                        <th>สถานะ</th>
                        <th>อัปเดต</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>
                                @if ($article->cover_image)
                                    <img class="thumb" src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">
                                @else
                                    <div class="thumb empty-thumb">No Image</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $article->title }}</strong>
                                <p class="muted" style="margin: 6px 0 0;">{{ $article->excerpt ?: 'ยังไม่มีคำอธิบายย่อ' }}</p>
                            </td>
                            <td>
                                <span class="badge {{ $article->status === 'published' ? 'published' : '' }}">
                                    {{ $article->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                                </span>
                            </td>
                            <td>{{ $article->updated_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="button secondary" href="{{ route('admin.articles.preview', $article) }}" target="_blank">Preview</a>
                                    <a class="button secondary" href="{{ route('admin.articles.edit', $article) }}">แก้ไข</a>
                                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('ต้องการลบบทความนี้ใช่ไหม?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger" type="submit">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <p class="muted" style="text-align: center; padding: 22px 0;">
                                    {{ $search !== '' ? 'ไม่พบบทความที่ตรงกับคำค้นหา' : 'ยังไม่มีบทความในรายการนี้' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $articles->links() }}
        </div>
    </section>
</x-admin-layout>
