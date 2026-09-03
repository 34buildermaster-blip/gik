<section class="page-hero reveal">
    <div class="shell"><p class="eyebrow">{{ $eyebrow ?? '34 BUILD MASTER CONSTRUCTION' }}</p><h1>{{ $title }}</h1><nav aria-label="เส้นทางหน้า"><a href="{{ route('site.home') }}">หน้าหลัก</a><span>/</span><strong>{{ $current ?? $title }}</strong></nav></div>
    <img src="{{ $image ?? url('/approach-homes/natural-modern.jpg') }}" alt="" aria-hidden="true">
</section>
