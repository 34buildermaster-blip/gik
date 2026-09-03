@extends('site.layout')
@section('title', 'บริการ | 34 Build Master')
@section('description', 'บริการออกแบบบ้าน สร้างบ้าน รีโนเวท บิวท์อิน ควบคุมงาน และที่ปรึกษาโครงการ')
@section('content')
@include('site.partials.page-hero', ['title' => 'บริการของเรา', 'current' => 'บริการ'])
<section class="section shell"><header class="section-heading reveal"><div><p class="eyebrow">OUR SERVICES</p><h2>ครบตั้งแต่แนวคิด<br>จนถึงพื้นที่จริง</h2></div><p>เลือกบริการที่ตรงกับโครงการ ทุกงานเริ่มจากการสำรวจโจทย์ วางแผนให้เห็นภาพ และควบคุมรายละเอียดตลอดกระบวนการ</p></header><div class="service-showcase stagger">@foreach($services as $service)<article><img src="{{ url($service['image']) }}" alt="{{ $service['title'] }}" loading="lazy"><div><span>{{ $service['number'] }}</span><h2>{{ $service['title'] }}</h2><p>{{ $service['description'] }}</p><a class="text-link" href="{{ route('site.contact') }}">ปรึกษางานนี้ →</a></div></article>@endforeach</div></section>
<section class="cta-strip"><div class="shell"><div><p class="eyebrow">NOT SURE WHERE TO START?</p><h2>ส่งข้อมูลเบื้องต้นให้ทีมช่วยประเมินประเภทงาน</h2></div><a class="button" href="{{ route('site.contact') }}">เริ่มปรึกษา →</a></div></section>
@include('site.partials.contact')
@endsection
