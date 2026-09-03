@extends('site.layout')
@section('title', 'คำถามที่พบบ่อย | 34 Build Master')
@section('description', 'คำตอบเรื่องการเริ่มโครงการ งบประมาณ กระบวนการก่อสร้าง การรับประกัน และระบบติดตามงาน')
@section('content')
@include('site.partials.page-hero', ['title' => 'คำถามที่พบบ่อย', 'current' => 'FAQ'])
<section class="faq-section section shell"><header class="section-heading reveal"><div><p class="eyebrow">FREQUENTLY ASKED QUESTIONS</p><h2>ข้อมูลที่ควรรู้<br>ก่อนเริ่มโครงการ</h2></div><p>เลือกหมวดคำถามที่สนใจ หากยังไม่พบคำตอบ ทีมงานพร้อมช่วยประเมินจากข้อมูลจริงของคุณ</p></header><div class="faq-layout"><nav class="faq-tabs" aria-label="หมวดคำถาม">@foreach($faqGroups as $label => $items)<button class="{{ $loop->first ? 'active' : '' }}" type="button" data-faq-tab="group-{{ $loop->index }}">{{ $label }}</button>@endforeach</nav><div class="faq-groups">@foreach($faqGroups as $label => $items)<section data-faq-group="group-{{ $loop->index }}" {{ $loop->first ? '' : 'hidden' }}>@foreach($items as $item)<details {{ $loop->first ? 'open' : '' }}><summary>{{ $item[0] }}<span>+</span></summary><p>{{ $item[1] }}</p></details>@endforeach</section>@endforeach</div></div></section>
@include('site.partials.contact')
@endsection
