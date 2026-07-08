<x-admin-layout title="บทความ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Articles</p>
            <h1>จัดการบทความ</h1>
            <p class="muted">เพิ่ม ลบ แก้ไขบทความ พร้อมรูปภาพประกอบและข้อมูล SEO</p>
        </div>
        <a class="button" href="{{ route('admin.articles.create') }}">+ เพิ่มบทความ</a>
    </div>

    <section class="card panel">
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
                                <p class="muted">ยังไม่มีบทความในระบบ</p>
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
