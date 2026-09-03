@extends('site.layout')
@section('title', 'อัปเดตหน้างาน | 34 Build Master')
@section('description', 'ตัวอย่างการอัปเดตความคืบหน้าหน้างานและมาตรฐานการสื่อสารของ 34 Build Master')
@section('content')
@include('site.partials.page-hero', ['title' => 'อัปเดตหน้างาน', 'current' => 'อัปเดตงาน', 'image' => url('/hero-construction.webp')])
<section class="section shell"><header class="section-heading reveal"><div><p class="eyebrow">PROJECT UPDATES</p><h2>ติดตามงานอย่างเข้าใจ<br>ในทุกขั้นตอน</h2></div><p>ลูกค้าในระบบจะเห็นเฉพาะข้อมูลที่ทีมงานตรวจสอบและอนุมัติแล้ว พร้อมรูปหน้างานและรายละเอียดที่เกี่ยวข้อง</p></header><div class="update-list stagger">@foreach($updates as $update)<article><img src="{{ url($update['image']) }}" alt="{{ $update['title'] }}" loading="lazy"><div><span>{{ $update['stage'] }}</span><h2>{{ $update['title'] }}</h2><p>{{ $update['detail'] }}</p><a class="text-link" href="{{ route('login.customer') }}">เข้าสู่ระบบเพื่อดูข้อมูลจริง →</a></div></article>@endforeach</div><div class="portal-cta reveal"><div><p class="eyebrow">CUSTOMER PORTAL</p><h2>เป็นลูกค้าของเรา?</h2><p>เข้าสู่ระบบเพื่อดูเปอร์เซ็นต์รวม รูปหน้างาน เอกสาร และข้อมูลอัปเดตเฉพาะโครงการของคุณ</p></div><a class="button" href="{{ route('login.customer') }}">ติดตามความคืบหน้า →</a></div></section>
@endsection
