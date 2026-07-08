<form class="card panel form" method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
    @csrf
    @if ($article->exists)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="title">ชื่อบทความ</label>
            <input id="title" name="title" type="text" value="{{ old('title', $article->title) }}" required>
        </div>

        <div class="field">
            <label for="status">สถานะ</label>
            <select id="status" name="status" required>
                <option value="draft" @selected(old('status', $article->status) === 'draft')>ฉบับร่าง</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>เผยแพร่</option>
            </select>
        </div>

        <div class="field full">
            <label for="excerpt">คำอธิบายสั้น</label>
            <textarea id="excerpt" name="excerpt" style="min-height: 110px;">{{ old('excerpt', $article->excerpt) }}</textarea>
        </div>

        <div class="field full">
            <label for="content">เนื้อหาบทความ</label>
            <div class="rich-editor" data-rich-editor>
                <div class="rich-toolbar" aria-label="เครื่องมือเขียนบทความ">
                    <button type="button" data-command="undo" title="Undo">↶</button>
                    <button type="button" data-command="redo" title="Redo">↷</button>
                    <select data-format-block title="รูปแบบข้อความ">
                        <option value="p">ย่อหน้า</option>
                        <option value="h2">หัวข้อใหญ่</option>
                        <option value="h3">หัวข้อรอง</option>
                        <option value="h4">หัวข้อย่อย</option>
                    </select>
                    <button type="button" data-command="bold" title="ตัวหนา"><strong>B</strong></button>
                    <button type="button" data-command="italic" title="ตัวเอียง"><em>I</em></button>
                    <button type="button" data-command="underline" title="ขีดเส้นใต้"><u>U</u></button>
                    <button type="button" data-command="strikeThrough" title="ขีดฆ่า"><s>S</s></button>
                    <input type="color" data-color-command="foreColor" value="#053920" title="สีตัวอักษร">
                    <input type="color" data-color-command="hiliteColor" value="#f6d97b" title="ไฮไลต์">
                    <button type="button" data-command="justifyLeft" title="ชิดซ้าย">≡</button>
                    <button type="button" data-command="justifyCenter" title="กึ่งกลาง">≣</button>
                    <button type="button" data-command="justifyRight" title="ชิดขวา">≡</button>
                    <button type="button" data-command="insertUnorderedList" title="Bullet list">• List</button>
                    <button type="button" data-command="insertOrderedList" title="Number list">1. List</button>
                    <button type="button" data-command="formatBlock" data-value="blockquote" title="Quote">Quote</button>
                    <button type="button" data-link title="ใส่ลิงก์">Link</button>
                    <button type="button" data-image title="ใส่รูปจาก URL">Image</button>
                    <button type="button" data-table title="เพิ่มตาราง">Table</button>
                    <button type="button" data-command="insertHorizontalRule" title="เส้นคั่น">HR</button>
                    <button type="button" data-command="removeFormat" title="ล้างรูปแบบ">Clear</button>
                    <button type="button" data-source-toggle title="ดู HTML">HTML</button>
                </div>
                <div class="rich-canvas" contenteditable="true" data-editor-canvas>{!! old('content', $article->content) !!}</div>
                <textarea id="content" name="content" required hidden>{{ old('content', $article->content) }}</textarea>
            </div>
            <p class="muted" style="margin: 0;">รองรับหัวข้อ รายการ ลิงก์ ตาราง สีตัวอักษร และรูปแบบ HTML สำหรับบทความ SEO</p>
        </div>

        <div class="field">
            <label for="cover_image">รูปภาพประกอบ</label>
            <input id="cover_image" name="cover_image" type="file" accept="image/*">
            <p class="muted" style="margin: 0;">รองรับ jpg, png, webp ขนาดไม่เกิน 4MB</p>
        </div>

        @if ($article->cover_image)
            <div class="field">
                <label>รูปปัจจุบัน</label>
                <img class="thumb" style="width: 220px; height: 140px;" src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">
            </div>
        @endif

        <div class="field">
            <label for="seo_title">SEO Title</label>
            <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title', $article->seo_title) }}">
        </div>

        <div class="field">
            <label for="seo_keywords">SEO Keywords</label>
            <input id="seo_keywords" name="seo_keywords" type="text" value="{{ old('seo_keywords', $article->seo_keywords) }}" placeholder="ออกแบบบ้าน, รีโนเวทบ้าน, บิวท์อิน">
        </div>

        <div class="field full">
            <label for="seo_description">SEO Description</label>
            <textarea id="seo_description" name="seo_description" style="min-height: 110px;">{{ old('seo_description', $article->seo_description) }}</textarea>
        </div>
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ $article->exists ? 'บันทึกการแก้ไข' : 'สร้างบทความ' }}</button>
        <a class="button secondary" href="{{ route('admin.articles.index') }}">ยกเลิก</a>
    </div>
</form>

<script>
    document.querySelectorAll('[data-rich-editor]').forEach((editor) => {
        const canvas = editor.querySelector('[data-editor-canvas]');
        const textarea = editor.querySelector('textarea[name="content"]');
        const sourceButton = editor.querySelector('[data-source-toggle]');
        let sourceMode = false;

        const sync = () => {
            textarea.value = sourceMode ? canvas.textContent : canvas.innerHTML;
        };
        const escapeAttribute = (value) => value
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');

        const focusCanvas = () => canvas.focus();

        editor.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('click', () => {
                focusCanvas();
                document.execCommand(button.dataset.command, false, button.dataset.value || null);
                sync();
            });
        });

        editor.querySelectorAll('[data-color-command]').forEach((input) => {
            input.addEventListener('input', () => {
                focusCanvas();
                document.execCommand(input.dataset.colorCommand, false, input.value);
                sync();
            });
        });

        editor.querySelector('[data-format-block]')?.addEventListener('change', (event) => {
            focusCanvas();
            document.execCommand('formatBlock', false, event.target.value);
            sync();
        });

        editor.querySelector('[data-link]')?.addEventListener('click', () => {
            focusCanvas();
            const url = window.prompt('ใส่ URL สำหรับลิงก์');

            if (url) {
                document.execCommand('createLink', false, url);
                sync();
            }
        });

        editor.querySelector('[data-image]')?.addEventListener('click', () => {
            focusCanvas();
            const url = window.prompt('ใส่ URL รูปภาพ');

            if (url) {
                const alt = window.prompt('คำอธิบายรูปภาพ', '') || '';
                const safeUrl = escapeAttribute(url);
                const safeAlt = escapeAttribute(alt);
                document.execCommand('insertHTML', false, `<figure><img src="${safeUrl}" alt="${safeAlt}"><figcaption>${safeAlt}</figcaption></figure><p><br></p>`);
                sync();
            }
        });

        editor.querySelector('[data-table]')?.addEventListener('click', () => {
            focusCanvas();
            const rows = Math.min(Math.max(parseInt(window.prompt('จำนวนแถว', '4') || '4', 10), 1), 12);
            const cols = Math.min(Math.max(parseInt(window.prompt('จำนวนคอลัมน์', '4') || '4', 10), 1), 8);
            let html = '<table><tbody>';

            for (let row = 0; row < rows; row++) {
                html += '<tr>';
                for (let col = 0; col < cols; col++) {
                    html += row === 0 ? '<th>หัวข้อ</th>' : '<td>ข้อมูล</td>';
                }
                html += '</tr>';
            }

            html += '</tbody></table><p><br></p>';
            document.execCommand('insertHTML', false, html);
            sync();
        });

        sourceButton?.addEventListener('click', () => {
            if (sourceMode) {
                canvas.innerHTML = canvas.textContent;
                sourceMode = false;
                sourceButton.classList.remove('is-active');
            } else {
                canvas.textContent = canvas.innerHTML;
                sourceMode = true;
                sourceButton.classList.add('is-active');
            }

            sync();
        });

        canvas.addEventListener('input', sync);
        canvas.closest('form')?.addEventListener('submit', sync);
        sync();
    });
</script>
