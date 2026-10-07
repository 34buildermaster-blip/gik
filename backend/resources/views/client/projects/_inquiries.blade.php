<section class="card panel project-inquiries" id="project-inquiries">
    <div class="panel-heading">
        <div><p class="eyebrow">PROJECT QUESTIONS</p><h2>สอบถามทีมงาน</h2><p>ถามเกี่ยวกับอัปเดต เอกสาร หรือรายละเอียดของโครงการ และติดตามคำตอบได้ในที่เดียว</p></div>
        <span class="client-update-count">{{ $project->inquiries->count() }} เรื่อง</span>
    </div>

    @unless($isStaff)
        <details class="inquiry-create" @if(request('ask_update') || $errors->hasAny(['subject', 'body', 'attachments.*'])) open @endif>
            <summary><x-ui-icon name="message-circle" /> ส่งคำถามใหม่</summary>
            <form class="inquiry-form" method="POST" action="{{ route('client.projects.inquiries.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="field"><label for="inquiry_subject">หัวข้อ</label><input id="inquiry_subject" name="subject" value="{{ old('subject') }}" maxlength="180" required></div>
                <div class="field"><label for="inquiry_update">เกี่ยวกับอัปเดต</label><select id="inquiry_update" name="project_update_id"><option value="">คำถามทั่วไปของโครงการ</option>@foreach($project->updates as $updateItem)<option value="{{ $updateItem->id }}" @selected((int) old('project_update_id', request('ask_update')) === $updateItem->id)>{{ $updateItem->title }}</option>@endforeach</select></div>
                <div class="field inquiry-wide"><label for="inquiry_body">รายละเอียด</label><textarea id="inquiry_body" name="body" rows="4" maxlength="5000" required>{{ old('body') }}</textarea></div>
                <div class="field inquiry-wide"><label for="inquiry_attachments">รูปประกอบ (สูงสุด 4 รูป)</label><input id="inquiry_attachments" name="attachments[]" type="file" accept="image/*" multiple></div>
                <button class="button" type="submit">ส่งให้ทีมงาน</button>
            </form>
        </details>
    @endunless

    <div class="inquiry-list">
        @forelse($project->inquiries as $inquiry)
            <details class="inquiry-thread" @if(request('inquiry') == $inquiry->id) open @endif>
                <summary>
                    <span><b>{{ $inquiry->subject }}</b><small>{{ $inquiry->customer?->name }}{{ $inquiry->projectUpdate ? ' · '.$inquiry->projectUpdate->title : '' }}</small></span>
                    <span class="inquiry-status status-{{ $inquiry->status }}">{{ $inquiryStatusLabels[$inquiry->status] }}</span>
                </summary>
                <div class="inquiry-messages">
                    @foreach($inquiry->messages as $message)
                        <article class="inquiry-message {{ $message->sender?->isStaff() ? 'is-staff' : 'is-customer' }}">
                            <header><strong>{{ $message->sender?->name ?: 'ผู้ใช้งาน' }}</strong><span>{{ $message->sender?->isStaff() ? 'ทีมงาน' : 'ลูกค้า' }}</span><time>{{ $message->created_at->locale('th')->diffForHumans() }}</time></header>
                            <p>{{ $message->body }}</p>
                            @if($message->attachments->isNotEmpty())<div class="inquiry-attachments">@foreach($message->attachments as $attachment)<a href="{{ route('project-inquiry-media.show', $attachment) }}" target="_blank"><img src="{{ route('project-inquiry-media.show', $attachment) }}" alt="รูปประกอบข้อความ"></a>@endforeach</div>@endif
                        </article>
                    @endforeach
                </div>

                @if($inquiry->status !== 'closed')
                    <form class="inquiry-reply" method="POST" action="{{ route('project-inquiries.messages.store', [$project, $inquiry]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="field"><label for="reply_{{ $inquiry->id }}">ตอบกลับ</label><textarea id="reply_{{ $inquiry->id }}" name="body" rows="3" maxlength="5000" required></textarea></div>
                        <div class="field"><label for="reply_files_{{ $inquiry->id }}">แนบรูป</label><input id="reply_files_{{ $inquiry->id }}" name="attachments[]" type="file" accept="image/*" multiple></div>
                        <button class="button" type="submit">ส่งข้อความ</button>
                    </form>
                @endif
                <form class="inquiry-status-form" method="POST" action="{{ route('project-inquiries.status', [$project, $inquiry]) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $inquiry->status === 'closed' ? 'open' : 'closed' }}">
                    <button type="submit">{{ $inquiry->status === 'closed' ? 'เปิดเรื่องอีกครั้ง' : 'ปิดเรื่องนี้' }}</button>
                </form>
            </details>
        @empty
            <div class="client-empty-state compact"><h3>ยังไม่มีคำถาม</h3><p>{{ $isStaff ? 'เมื่อลูกค้าส่งคำถาม รายการจะปรากฏตรงนี้' : 'ส่งคำถามได้จากปุ่มด้านบน ทีมงานจะตอบกลับในหน้านี้และแจ้งผ่าน LINE' }}</p></div>
        @endforelse
    </div>
</section>
