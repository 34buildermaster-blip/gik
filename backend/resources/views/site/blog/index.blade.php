@extends('site.layout')
@section('title', 'บทความ | 34 Build Master')
@section('description', 'บทความเรื่องบ้าน การออกแบบ วัสดุ และการวางแผนก่อสร้างจาก 34 Build Master')
@section('content')
@include('site.partials.page-hero', ['title' => 'บทความ', 'current' => 'บทความ', 'image' => url('/bg-material-board.webp')])
<section class="section shell"><header class="section-heading reveal"><div><p class="eyebrow">KNOWLEDGE & IDEAS</p><h2>เรื่องบ้านที่ช่วยให้<br>ตัดสินใจได้ชัดขึ้น</h2></div><p>แนวคิดและความรู้จากประสบการณ์ออกแบบ วางแผน และดูแลงานก่อสร้างจริง</p></header><div class="article-grid stagger">@forelse($articles as $article)<article><a href="{{ route('site.blog.show', $article['slug']) }}"><img src="{{ $article['image'] }}" alt="{{ $article['cover_alt'] }}" loading="lazy"></a><div><span>{{ $article['date'] }} · อ่าน {{ $article['read_time'] }}</span><h2><a href="{{ route('site.blog.show', $article['slug']) }}">{{ $article['title'] }}</a></h2><p>{{ $article['excerpt'] }}</p><a class="text-link" href="{{ route('site.blog.show', $article['slug']) }}">อ่านบทความ →</a></div></article>@empty<div class="empty-state"><h2>ยังไม่มีบทความที่เผยแพร่</h2><p>บทความใหม่จะปรากฏที่นี่เมื่อเผยแพร่จากระบบหลังบ้าน</p></div>@endforelse</div></section>
@endsection
