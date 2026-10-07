<x-admin-layout title="ปฏิทินโครงการ | 34 Build Master">
    <div class="topbar calendar-topbar">
        <div>
            <p class="eyebrow">PROJECT CALENDAR</p>
            <h1>ปฏิทินโครงการ</h1>
            <p class="muted" style="margin:7px 0 0;">รวมวันนัดตรวจ ประชุม กำหนดจบขั้นตอน และกำหนดแก้ไขงานไว้ในหน้าเดียว</p>
        </div>
        <a class="button secondary" href="#calendar-create"><x-ui-icon name="calendar" /> เพิ่มนัดหมาย</a>
    </div>

    <section class="calendar-summary-strip" aria-label="สรุปปฏิทิน">
        <div><span>เดือนที่แสดง</span><strong>{{ $month->locale('th')->translatedFormat('F Y') }}</strong></div>
        <div><span>นัดหมายที่กำลังจะถึง</span><strong>{{ $upcomingEvents->count() }}</strong></div>
        <div><span>โครงการที่เข้าถึงได้</span><strong>{{ $projects->count() }}</strong></div>
        <a href="{{ route('admin.dashboard') }}">กลับ Dashboard <x-ui-icon name="arrow-right" /></a>
    </section>

    <div class="calendar-workspace">
        <section class="card panel calendar-panel">
            <header class="calendar-toolbar">
                <a class="calendar-nav-button" href="{{ route('admin.calendar.index', ['month' => $month->subMonth()->format('Y-m')]) }}" aria-label="เดือนก่อนหน้า"><x-ui-icon name="chevron-left" /></a>
                <div><span>{{ $month->format('Y') }}</span><h2>{{ $month->locale('th')->translatedFormat('F') }}</h2></div>
                <a class="calendar-nav-button" href="{{ route('admin.calendar.index', ['month' => $month->addMonth()->format('Y-m')]) }}" aria-label="เดือนถัดไป"><x-ui-icon name="chevron-right" /></a>
            </header>

            <div class="calendar-weekdays" aria-hidden="true">
                @foreach(['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.'] as $weekday)<span>{{ $weekday }}</span>@endforeach
            </div>
            <div class="calendar-grid">
                @foreach($days as $day)
                    @php($dayItems = $itemsByDate->get($day->toDateString(), collect()))
                    <article class="calendar-day {{ $dayItems->isNotEmpty() ? 'has-items' : '' }} {{ $day->month !== $month->month ? 'is-outside' : '' }} {{ $day->isToday() ? 'is-today' : '' }}">
                        <header><span>{{ $day->day }}</span>@if($day->isToday())<small>วันนี้</small>@endif</header>
                        <div class="calendar-day-items">
                            @foreach($dayItems->take(4) as $item)
                                @if($item['event'])
                                    <a class="calendar-item kind-{{ $item['kind'] }} type-{{ $item['type'] }}" href="{{ route('admin.calendar.index', ['month' => $month->format('Y-m'), 'event' => $item['event']->id]) }}#event-{{ $item['event']->id }}">
                                        <span>{{ $item['time'] ?: $item['project'] }}</span><strong>{{ $item['title'] }}</strong>
                                    </a>
                                @else
                                    <span class="calendar-item kind-{{ $item['kind'] }}"><span>{{ $item['project'] }}</span><strong>{{ $item['title'] }}</strong></span>
                                @endif
                            @endforeach
                            @if($dayItems->count() > 4)<small class="calendar-more">อีก {{ $dayItems->count() - 4 }} รายการ</small>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="calendar-side">
            <section class="card panel" id="calendar-create">
                <div class="panel-heading"><div><p class="eyebrow">NEW EVENT</p><h2>เพิ่มนัดหมาย</h2><p>ผู้เกี่ยวข้องจะได้รับแจ้งเตือนทันทีและก่อนนัด 24 ชั่วโมง</p></div></div>
                <form class="calendar-form" method="POST" action="{{ route('admin.calendar.store') }}">
                    @csrf
                    <div class="field"><label for="calendar_project">โครงการ <span class="required-mark">*</span></label><select id="calendar_project" name="project_id" required><option value="">เลือกโครงการ</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((int) old('project_id', $selectedProjectId) === $project->id)>{{ $project->code }} · {{ $project->name }}</option>@endforeach</select></div>
                    <div class="field"><label for="calendar_title">หัวข้อนัดหมาย <span class="required-mark">*</span></label><input id="calendar_title" name="title" value="{{ old('title') }}" maxlength="180" required placeholder="เช่น ตรวจรับงานโครงสร้าง"></div>
                    <div class="calendar-form-row">
                        <div class="field"><label for="calendar_type">ประเภท</label><select id="calendar_type" name="type" required>@foreach($typeLabels as $value => $label)<option value="{{ $value }}" @selected(old('type', 'site_inspection') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="field"><label for="calendar_assignee">ผู้รับผิดชอบ</label><select id="calendar_assignee" name="assigned_to"><option value="">ไม่ระบุ</option>@foreach($staffUsers as $staff)<option value="{{ $staff->id }}" @selected((int) old('assigned_to') === $staff->id)>{{ $staff->name }}</option>@endforeach</select></div>
                    </div>
                    <div class="calendar-form-row">
                        <div class="field"><label for="calendar_starts">เริ่ม <span class="required-mark">*</span></label><input id="calendar_starts" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required></div>
                        <div class="field"><label for="calendar_ends">สิ้นสุด</label><input id="calendar_ends" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}"></div>
                    </div>
                    <div class="field"><label for="calendar_location">สถานที่</label><input id="calendar_location" name="location" value="{{ old('location') }}" maxlength="255" placeholder="หน้างาน หรือห้องประชุม"></div>
                    <div class="field"><label for="calendar_description">รายละเอียด</label><textarea id="calendar_description" name="description" rows="3" maxlength="3000">{{ old('description') }}</textarea></div>
                    <label class="calendar-checkbox"><input name="customer_visible" type="checkbox" value="1" @checked(old('customer_visible', true))><span>แสดงนัดหมายนี้ให้ลูกค้าเห็น</span></label>
                    @if($errors->calendar->any())<div class="form-errors" role="alert">{{ $errors->calendar->first() }}</div>@endif
                    <button class="button" type="submit"><x-ui-icon name="calendar" /> บันทึกนัดหมาย</button>
                </form>
            </section>
        </aside>
    </div>

    <section class="card panel calendar-upcoming-panel">
        <div class="panel-heading"><div><p class="eyebrow">UPCOMING</p><h2>นัดหมายที่กำลังจะถึง</h2><p>แก้ไขเวลา ผู้รับผิดชอบ สถานะ และข้อมูลที่ลูกค้ามองเห็นได้จากรายการนี้</p></div><span class="client-update-count">{{ $upcomingEvents->count() }} รายการ</span></div>
        <div class="calendar-event-list">
            @forelse($upcomingEvents as $event)
                <details class="calendar-event-card type-{{ $event->type }}" id="event-{{ $event->id }}" @if($selectedEventId === $event->id) open @endif>
                    <summary>
                        <time><strong>{{ $event->starts_at->format('d') }}</strong><span>{{ $event->starts_at->locale('th')->translatedFormat('M') }}</span></time>
                        <div><span>{{ $event->project->code }} · {{ $typeLabels[$event->type] }}</span><h3>{{ $event->title }}</h3><small>{{ $event->starts_at->format('H:i') }} น.{{ $event->location ? ' · '.$event->location : '' }}{{ $event->assignee ? ' · '.$event->assignee->name : '' }}</small></div>
                        <b class="status-{{ $event->status }}">{{ $statusLabels[$event->status] }}</b>
                        <x-ui-icon name="chevron-right" />
                    </summary>
                    <form class="calendar-form calendar-edit-form" method="POST" action="{{ route('admin.calendar.update', $event) }}">
                        @csrf @method('PUT')
                        <div class="calendar-form-row"><div class="field"><label>โครงการ</label><select name="project_id" required>@foreach($projects as $project)<option value="{{ $project->id }}" @selected($event->project_id === $project->id)>{{ $project->code }} · {{ $project->name }}</option>@endforeach</select></div><div class="field"><label>สถานะ</label><select name="status" required>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($event->status === $value)>{{ $label }}</option>@endforeach</select></div></div>
                        <div class="field"><label>หัวข้อ</label><input name="title" value="{{ $event->title }}" maxlength="180" required></div>
                        <div class="calendar-form-row"><div class="field"><label>ประเภท</label><select name="type" required>@foreach($typeLabels as $value => $label)<option value="{{ $value }}" @selected($event->type === $value)>{{ $label }}</option>@endforeach</select></div><div class="field"><label>ผู้รับผิดชอบ</label><select name="assigned_to"><option value="">ไม่ระบุ</option>@foreach($staffUsers as $staff)<option value="{{ $staff->id }}" @selected($event->assigned_to === $staff->id)>{{ $staff->name }}</option>@endforeach</select></div></div>
                        <div class="calendar-form-row"><div class="field"><label>เริ่ม</label><input name="starts_at" type="datetime-local" value="{{ $event->starts_at->format('Y-m-d\TH:i') }}" required></div><div class="field"><label>สิ้นสุด</label><input name="ends_at" type="datetime-local" value="{{ $event->ends_at?->format('Y-m-d\TH:i') }}"></div></div>
                        <div class="field"><label>สถานที่</label><input name="location" value="{{ $event->location }}" maxlength="255"></div>
                        <div class="field"><label>รายละเอียด</label><textarea name="description" rows="3" maxlength="3000">{{ $event->description }}</textarea></div>
                        <label class="calendar-checkbox"><input name="customer_visible" type="checkbox" value="1" @checked($event->customer_visible)><span>แสดงนัดหมายนี้ให้ลูกค้าเห็น</span></label>
                        <button class="button" type="submit">บันทึกการเปลี่ยนแปลง</button>
                    </form>
                </details>
            @empty
                <div class="dashboard-empty"><strong>ยังไม่มีนัดหมายที่กำลังจะถึง</strong><a href="#calendar-create">เพิ่มนัดหมายแรก</a></div>
            @endforelse
        </div>
    </section>
</x-admin-layout>
