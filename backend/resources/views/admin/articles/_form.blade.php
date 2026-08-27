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
                    <button type="button" data-command="undo" title="Undo" aria-label="Undo">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7H4v5"></path><path d="M4 12c2.2-4.4 6.5-6.2 10.3-4.8 3.9 1.4 5.8 5.6 4.4 9.4"></path></svg>
                    </button>
                    <button type="button" data-command="redo" title="Redo" aria-label="Redo">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 7h5v5"></path><path d="M20 12c-2.2-4.4-6.5-6.2-10.3-4.8-3.9 1.4-5.8 5.6-4.4 9.4"></path></svg>
                    </button>
                    <select data-format-block title="รูปแบบข้อความ">
                        <option value="p">ย่อหน้า</option>
                        <option value="h2">หัวข้อใหญ่</option>
                        <option value="h3">หัวข้อรอง</option>
                        <option value="h4">หัวข้อย่อย</option>
                    </select>
                    <select class="toolbar-font-select" data-font-family title="แบบฟอนต์">
                        <option value="">Font</option>
                        <option value="'LINE Seed Sans TH', sans-serif">LINE Seed Sans TH</option>
                        <option value="'Prompt', sans-serif">Prompt</option>
                        <option value="'Tahoma', sans-serif">Tahoma</option>
                        <option value="'Arial', sans-serif">Arial</option>
                        <option value="'Georgia', serif">Georgia</option>
                    </select>
                    <select class="toolbar-size-select" data-font-size title="ขนาดตัวอักษร">
                        <option value="">Size</option>
                        <option value="14px">14</option>
                        <option value="16px">16</option>
                        <option value="18px">18</option>
                        <option value="20px">20</option>
                        <option value="24px">24</option>
                        <option value="30px">30</option>
                        <option value="36px">36</option>
                    </select>
                    <button type="button" data-command="bold" title="ตัวหนา" aria-label="ตัวหนา"><strong aria-hidden="true">B</strong></button>
                    <button type="button" data-command="italic" title="ตัวเอียง" aria-label="ตัวเอียง"><em aria-hidden="true">I</em></button>
                    <button type="button" data-command="underline" title="ขีดเส้นใต้" aria-label="ขีดเส้นใต้"><u aria-hidden="true">U</u></button>
                    <button type="button" data-command="strikeThrough" title="ขีดฆ่า" aria-label="ขีดฆ่า"><s aria-hidden="true">S</s></button>
                    <input type="color" data-color-command="foreColor" value="#053920" title="สีตัวอักษร">
                    <input type="color" data-color-command="hiliteColor" value="#f6d97b" title="ไฮไลต์">
                    <button type="button" data-command="justifyLeft" title="ชิดซ้าย" aria-label="ชิดซ้าย">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"></path><path d="M4 10h10"></path><path d="M4 14h16"></path><path d="M4 18h10"></path></svg>
                    </button>
                    <button type="button" data-command="justifyCenter" title="กึ่งกลาง" aria-label="กึ่งกลาง">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"></path><path d="M7 10h10"></path><path d="M4 14h16"></path><path d="M7 18h10"></path></svg>
                    </button>
                    <button type="button" data-command="justifyRight" title="ชิดขวา" aria-label="ชิดขวา">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"></path><path d="M10 10h10"></path><path d="M4 14h16"></path><path d="M10 18h10"></path></svg>
                    </button>
                    <button type="button" data-command="insertUnorderedList" title="Bullet list" aria-label="Bullet list">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg>
                    </button>
                    <button type="button" data-command="insertOrderedList" title="Number list" aria-label="Number list">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6h11"></path><path d="M10 12h11"></path><path d="M10 18h11"></path><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M4 14h2l-2 4h2"></path></svg>
                    </button>
                    <button type="button" data-command="formatBlock" data-value="blockquote" title="Quote" aria-label="Quote">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 10h-4c0-4 2-6 6-6v3c-2 0-2 1-2 3v5h-4v-5"></path><path d="M20 10h-4c0-4 2-6 6-6v3c-2 0-2 1-2 3v5h-4v-5"></path></svg>
                    </button>
                    <button type="button" data-link title="ใส่ลิงก์" aria-label="ใส่ลิงก์">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"></path><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"></path></svg>
                    </button>
                    <button type="button" data-upload-markdown title="นำเข้าไฟล์ Markdown" aria-label="นำเข้าไฟล์ Markdown">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v5h5"></path><path d="M19 21H5V3h9l5 5v13z"></path><path d="M8 16v-5l2 3 2-3v5"></path><path d="M15 11v5"></path><path d="M13 14l2 2 2-2"></path></svg>
                    </button>
                    <button type="button" data-upload-image title="อัปโหลดรูปจากเครื่อง" aria-label="อัปโหลดรูปจากเครื่อง">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19h14V5H5v14z"></path><path d="M8 14l2.5-3 2 2.5 1.5-2 3 4.5"></path><path d="M9 8h.01"></path><path d="M12 4v6"></path><path d="M9 7l3-3 3 3"></path></svg>
                    </button>
                    <button type="button" data-upload-video title="อัปโหลดวิดีโอจากเครื่อง" aria-label="อัปโหลดวิดีโอจากเครื่อง">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h11v10H4z"></path><path d="M15 11l5-3v8l-5-3"></path><path d="M8 5l3-3 3 3"></path><path d="M11 2v8"></path></svg>
                    </button>
                    <button type="button" data-image title="ใส่รูปจาก URL" aria-label="ใส่รูปจาก URL">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16V5H4v14z"></path><path d="M8 14l2.5-3 2 2.5 1.5-2 3 4.5"></path><path d="M9 8h.01"></path><path d="M15 8h5"></path><path d="M18 5v6"></path></svg>
                    </button>
                    <button type="button" data-table title="เพิ่มตาราง" aria-label="เพิ่มตาราง">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"></path><path d="M4 10h16"></path><path d="M4 15h16"></path><path d="M10 5v14"></path><path d="M16 5v14"></path></svg>
                    </button>
                    <button type="button" data-command="insertHorizontalRule" title="เส้นคั่น" aria-label="เส้นคั่น">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h16"></path></svg>
                    </button>
                    <button type="button" data-command="removeFormat" title="ล้างรูปแบบ" aria-label="ล้างรูปแบบ">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16l8-8 6 6-5 5H7l-3-3z"></path><path d="M14 6l4 4"></path><path d="M4 21h16"></path></svg>
                    </button>
                    <button type="button" data-source-toggle title="ดู HTML" aria-label="ดู HTML">
                        <svg class="tool-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 9l-4 3 4 3"></path><path d="M16 9l4 3-4 3"></path><path d="M14 5l-4 14"></path></svg>
                    </button>
                </div>
                <input type="file" data-markdown-input accept=".md,.markdown,.txt,text/markdown,text/plain" hidden>
                <input type="file" data-media-input="image" accept="image/*" hidden>
                <input type="file" data-media-input="video" accept="video/mp4,video/webm,video/quicktime" hidden>
                <div class="rich-canvas" contenteditable="true" data-editor-canvas>{!! $editorContent !!}</div>
                <textarea id="content" name="content" required hidden>{{ $editorContent }}</textarea>
            </div>
            <p class="muted" style="margin: 0;">รองรับไฟล์ .md, หัวข้อ ฟอนต์ ขนาดตัวอักษร รายการ ลิงก์ ตาราง รูปภาพ วิดีโอ สีตัวอักษร และรูปแบบ HTML สำหรับบทความ SEO</p>
        </div>

        <div class="field">
            <label for="cover_image">รูปภาพประกอบ</label>
            <input id="cover_image" name="cover_image" type="file" accept="image/*">
            <p class="muted" style="margin: 0;">รองรับ jpg, png, webp ขนาดไม่เกิน 4MB</p>
        </div>

        @if ($article->coverUrl())
            <div class="field">
                <label>รูปปัจจุบัน</label>
                <img class="thumb" style="width: 220px; height: 140px;" src="{{ $article->coverUrl() }}" alt="{{ $article->title }}">
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
        const markdownUrl = @json(route('admin.articles.markdown'));
        const uploadUrl = @json(route('admin.articles.media'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const form = editor.closest('form');
        const titleInput = form?.querySelector('input[name="title"]');
        const excerptInput = form?.querySelector('textarea[name="excerpt"]');
        const markdownInput = editor.querySelector('[data-markdown-input]');
        const imageInput = editor.querySelector('[data-media-input="image"]');
        const videoInput = editor.querySelector('[data-media-input="video"]');
        const markdownButton = editor.querySelector('[data-upload-markdown]');
        const imageUploadButton = editor.querySelector('[data-upload-image]');
        const videoUploadButton = editor.querySelector('[data-upload-video]');
        let sourceMode = false;
        let savedRange = null;

        const fontSizeMap = {
            1: '12px',
            2: '14px',
            3: '16px',
            4: '18px',
            5: '24px',
            6: '30px',
            7: '36px',
        };

        const normalizeEditorHtml = (html) => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;

            wrapper.querySelectorAll('font').forEach((font) => {
                const span = document.createElement('span');
                const styles = [];
                const existingStyle = font.getAttribute('style');
                const face = font.getAttribute('face');
                const color = font.getAttribute('color');
                const size = font.getAttribute('size');

                if (existingStyle) {
                    styles.push(existingStyle.replace(/;$/, ''));
                }

                if (face) {
                    styles.push(`font-family: ${face}`);
                }

                if (color) {
                    styles.push(`color: ${color}`);
                }

                if (size && fontSizeMap[size]) {
                    styles.push(`font-size: ${fontSizeMap[size]}`);
                }

                if (styles.length > 0) {
                    span.setAttribute('style', styles.join('; '));
                }

                while (font.firstChild) {
                    span.appendChild(font.firstChild);
                }

                font.replaceWith(span);
            });

            return wrapper.innerHTML;
        };

        const sync = () => {
            textarea.value = sourceMode ? canvas.textContent : normalizeEditorHtml(canvas.innerHTML);
        };
        const escapeAttribute = (value) => value
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');

        const focusCanvas = () => canvas.focus();
        const getCanvasSelection = () => {
            const selection = window.getSelection();

            if (!selection || selection.rangeCount === 0) {
                return null;
            }

            const range = selection.getRangeAt(0);

            if (!canvas.contains(range.commonAncestorContainer)) {
                return null;
            }

            return { selection, range };
        };
        const selectionIsInsideCanvas = () => {
            return getCanvasSelection() !== null;
        };
        const saveCanvasSelection = () => {
            const currentSelection = getCanvasSelection();

            if (currentSelection) {
                savedRange = currentSelection.range.cloneRange();
            }
        };
        const restoreCanvasSelection = () => {
            if (!savedRange || !canvas.contains(savedRange.commonAncestorContainer)) {
                return false;
            }

            canvas.focus({ preventScroll: true });

            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(savedRange.cloneRange());

            return true;
        };
        const applyInlineStyle = (style) => {
            if (sourceMode) {
                window.alert('กรุณาปิดโหมด HTML ก่อนปรับรูปแบบข้อความ');
                return;
            }

            if (!restoreCanvasSelection() || !selectionIsInsideCanvas()) {
                window.alert('เลือกข้อความในบทความก่อนปรับฟอนต์หรือขนาดตัวอักษร');
                return;
            }

            const { selection, range } = getCanvasSelection();
            const span = document.createElement('span');
            span.setAttribute('style', style);

            if (range.collapsed) {
                span.appendChild(document.createTextNode('\u200b'));
                range.insertNode(span);
                range.setStart(span.firstChild, 1);
                range.setEnd(span.firstChild, 1);
            } else {
                span.appendChild(range.extractContents());
                range.insertNode(span);
                range.selectNodeContents(span);
            }

            selection.removeAllRanges();
            selection.addRange(range);
            savedRange = range.cloneRange();
            sync();
        };
        const insertHtml = (html) => {
            focusCanvas();
            document.execCommand('insertHTML', false, html);
            sync();
        };
        const getSelectedPlainText = (range) => {
            const wrapper = document.createElement('div');
            wrapper.appendChild(range.cloneContents());

            wrapper.querySelectorAll('br').forEach((br) => br.replaceWith('\n'));
            wrapper.querySelectorAll('p, div, li, tr, h2, h3, h4').forEach((element) => {
                element.appendChild(document.createTextNode('\n'));
            });

            return wrapper.textContent
                .replace(/\u00a0/g, ' ')
                .replace(/\r/g, '')
                .replace(/\n{3,}/g, '\n\n')
                .trim();
        };
        const splitTableLine = (line, delimiter) => {
            if (delimiter === 'pipe') {
                return line.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|');
            }

            if (delimiter === 'tab') {
                return line.split('\t');
            }

            if (delimiter === 'comma') {
                return line.split(',');
            }

            return line.split(/\s{2,}/);
        };
        const parseSelectedTableRows = (text) => {
            const lines = text
                .split('\n')
                .map((line) => line.trim())
                .filter(Boolean);

            if (lines.length === 0) {
                return [];
            }

            const delimiter = lines.some((line) => line.includes('\t'))
                ? 'tab'
                : lines.some((line) => line.includes('|'))
                    ? 'pipe'
                    : lines.some((line) => line.includes(','))
                        ? 'comma'
                        : 'spaces';

            const rows = lines
                .map((line) => splitTableLine(line, delimiter)
                    .map((cell) => cell.trim())
                    .filter((cell) => cell.length > 0))
                .filter((row) => !row.every((cell) => /^:?-{3,}:?$/.test(cell)))
                .filter((row) => row.length > 0);

            const maxColumns = Math.max(...rows.map((row) => row.length));

            if (rows.length < 2 && maxColumns < 2) {
                return [];
            }

            return rows.map((row) => {
                while (row.length < maxColumns) {
                    row.push('');
                }

                return row;
            });
        };
        const buildTableHtml = (rows) => {
            let html = '<table><tbody>';

            rows.forEach((row, rowIndex) => {
                html += '<tr>';
                row.forEach((cell) => {
                    const safeCell = escapeAttribute(cell);
                    html += rowIndex === 0 ? `<th>${safeCell}</th>` : `<td>${safeCell}</td>`;
                });
                html += '</tr>';
            });

            return `${html}</tbody></table><p><br></p>`;
        };
        const replaceSelectionWithHtml = (range, html) => {
            const fragment = range.createContextualFragment(html);
            range.deleteContents();
            range.insertNode(fragment);
            sync();
        };
        const uploadMedia = async (input, button) => {
            if (sourceMode) {
                window.alert('กรุณาปิดโหมด HTML ก่อนอัปโหลดไฟล์');
                input.value = '';
                return;
            }

            const file = input.files?.[0];

            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('media', file);
            button.disabled = true;
            button.classList.add('is-loading');

            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Upload failed');
                }

                const media = await response.json();
                const safeUrl = escapeAttribute(media.url || '');
                const safeName = escapeAttribute(media.name || file.name || 'media');
                const safeMimeType = escapeAttribute(media.mimeType || file.type || '');

                if (media.type === 'video') {
                    insertHtml(`<figure><video controls preload="metadata"><source src="${safeUrl}" type="${safeMimeType}">เบราว์เซอร์ไม่รองรับวิดีโอ</video><figcaption>${safeName}</figcaption></figure><p><br></p>`);
                } else {
                    insertHtml(`<figure><img src="${safeUrl}" alt="${safeName}"><figcaption>${safeName}</figcaption></figure><p><br></p>`);
                }
            } catch (error) {
                window.alert('อัปโหลดไฟล์ไม่สำเร็จ กรุณาตรวจสอบชนิดไฟล์หรือขนาดไฟล์แล้วลองใหม่');
            } finally {
                input.value = '';
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        };
        const uploadMarkdown = async (file, button) => {
            if (sourceMode) {
                window.alert('กรุณาปิดโหมด HTML ก่อนนำเข้าไฟล์ Markdown');
                return;
            }

            if (!file) {
                return;
            }

            const hasContent = canvas.textContent.trim().length > 0;
            const shouldReplace = hasContent
                ? window.confirm('แทนที่เนื้อหาปัจจุบันด้วยไฟล์ Markdown นี้ไหม? กด Cancel เพื่อแทรกต่อท้าย')
                : true;
            const formData = new FormData();
            formData.append('markdown', file);

            if (button) {
                button.disabled = true;
                button.classList.add('is-loading');
            }

            try {
                const response = await fetch(markdownUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Import failed');
                }

                const article = await response.json();

                if (shouldReplace) {
                    canvas.innerHTML = article.html || '';
                    sync();
                } else {
                    insertHtml(`${article.html || ''}<p><br></p>`);
                }

                if (titleInput && !titleInput.value.trim() && article.title) {
                    titleInput.value = article.title;
                }

                if (excerptInput && !excerptInput.value.trim() && article.excerpt) {
                    excerptInput.value = article.excerpt;
                }
            } catch (error) {
                window.alert('นำเข้าไฟล์ Markdown ไม่สำเร็จ กรุณาตรวจสอบไฟล์ .md แล้วลองใหม่');
            } finally {
                if (markdownInput) {
                    markdownInput.value = '';
                }

                if (button) {
                    button.disabled = false;
                    button.classList.remove('is-loading');
                }
            }
        };

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

        editor.querySelector('[data-font-family]')?.addEventListener('change', (event) => {
            const fontFamily = event.target.value;

            if (fontFamily) {
                applyInlineStyle(`font-family: ${fontFamily}`);
                event.target.value = '';
            }
        });

        editor.querySelector('[data-font-size]')?.addEventListener('change', (event) => {
            const fontSize = event.target.value;

            if (fontSize) {
                applyInlineStyle(`font-size: ${fontSize}`);
                event.target.value = '';
            }
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
                insertHtml(`<figure><img src="${safeUrl}" alt="${safeAlt}"><figcaption>${safeAlt}</figcaption></figure><p><br></p>`);
            }
        });

        markdownButton?.addEventListener('click', () => markdownInput?.click());
        markdownInput?.addEventListener('change', () => uploadMarkdown(markdownInput.files?.[0], markdownButton));
        imageUploadButton?.addEventListener('click', () => imageInput?.click());
        videoUploadButton?.addEventListener('click', () => videoInput?.click());
        imageInput?.addEventListener('change', () => uploadMedia(imageInput, imageUploadButton));
        videoInput?.addEventListener('change', () => uploadMedia(videoInput, videoUploadButton));

        editor.querySelector('[data-table]')?.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        editor.querySelector('[data-table]')?.addEventListener('click', () => {
            const activeSelection = getCanvasSelection();
            const selectedText = activeSelection && !activeSelection.range.collapsed
                ? getSelectedPlainText(activeSelection.range)
                : '';
            const selectedRows = parseSelectedTableRows(selectedText);

            if (selectedRows.length > 0 && activeSelection) {
                replaceSelectionWithHtml(activeSelection.range, buildTableHtml(selectedRows));
                return;
            }

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

        document.addEventListener('selectionchange', () => {
            if (document.activeElement === canvas) {
                saveCanvasSelection();
            }
        });
        editor.querySelector('.rich-toolbar')?.addEventListener('pointerdown', saveCanvasSelection, true);
        editor.querySelector('.rich-toolbar')?.addEventListener('mousedown', saveCanvasSelection, true);
        canvas.addEventListener('blur', saveCanvasSelection);
        canvas.addEventListener('mouseup', saveCanvasSelection);
        canvas.addEventListener('keyup', saveCanvasSelection);
        canvas.addEventListener('touchend', saveCanvasSelection);
        canvas.addEventListener('input', () => {
            saveCanvasSelection();
            sync();
        });
        canvas.addEventListener('dragover', (event) => {
            if ([...(event.dataTransfer?.items || [])].some((item) => item.kind === 'file')) {
                event.preventDefault();
                canvas.classList.add('is-dragging');
            }
        });
        canvas.addEventListener('dragleave', () => canvas.classList.remove('is-dragging'));
        canvas.addEventListener('drop', (event) => {
            const file = event.dataTransfer?.files?.[0];

            if (file && /\.(md|markdown|txt)$/i.test(file.name)) {
                event.preventDefault();
                canvas.classList.remove('is-dragging');
                uploadMarkdown(file, markdownButton);
            }
        });
        form?.addEventListener('submit', sync);
        sync();
    });
</script>
