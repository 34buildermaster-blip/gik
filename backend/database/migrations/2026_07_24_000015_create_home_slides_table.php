<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('section', 24)->index();
            $table->foreignId('stored_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->string('title_line_2')->nullable();
            $table->text('description')->nullable();
            $table->string('label')->nullable();
            $table->string('alt_text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section', 'is_active', 'sort_order']);
        });

        $now = now();
        DB::table('home_slides')->insert([
            [
                'section' => 'hero',
                'image_path' => '/hero-construction.png',
                'eyebrow' => 'DESIGN · BUILD · RENOVATE',
                'title' => 'สร้างพื้นที่ที่ดี',
                'title_line_2' => 'ให้ชีวิตเดินหน้าได้จริง',
                'description' => 'ออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน พร้อมดูแลรายละเอียดตั้งแต่แนวคิดจนถึงวันส่งมอบ',
                'label' => 'BUILD WITH CLARITY',
                'alt_text' => 'ทีมงาน 34 Build Master Construction ตรวจแบบบ้านก่อนเริ่มงาน',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'hero',
                'image_path' => '/approach-homes/modern.jpg',
                'eyebrow' => 'ARCHITECTURAL DESIGN',
                'title' => 'บ้านที่สะท้อนตัวตน',
                'title_line_2' => 'และตอบโจทย์ทุกวัน',
                'description' => 'เริ่มจากการเข้าใจชีวิตจริง วางฟังก์ชัน และพัฒนาแบบให้สมดุลทั้งความสวยงาม งบประมาณ และการใช้งาน',
                'label' => 'MODERN RESIDENCE',
                'alt_text' => 'บ้านพักอาศัยสไตล์โมเดิร์น',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'hero',
                'image_path' => '/approach-homes/contemporary.jpg',
                'eyebrow' => 'CONSTRUCTION MANAGEMENT',
                'title' => 'ทุกขั้นตอนชัดเจน',
                'title_line_2' => 'ตั้งแต่แบบถึงส่งมอบ',
                'description' => 'วางแผนงานก่อสร้าง ควบคุมคุณภาพ และสื่อสารความคืบหน้าอย่างเป็นระบบ เพื่อให้ทุกการตัดสินใจมั่นใจขึ้น',
                'label' => 'QUALITY IN DETAIL',
                'alt_text' => 'บ้านร่วมสมัยที่ออกแบบอย่างพิถีพิถัน',
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'hero',
                'image_path' => '/approach-homes/natural-modern.jpg',
                'eyebrow' => 'RENOVATION · INTERIOR',
                'title' => 'เปลี่ยนพื้นที่เดิม',
                'title_line_2' => 'ให้กลับมาน่าอยู่กว่าเดิม',
                'description' => 'ปรับโครงสร้าง พื้นที่ และบรรยากาศภายในอย่างเข้าใจข้อจำกัดเดิม พร้อมต่อยอดให้เหมาะกับชีวิตในปัจจุบัน',
                'label' => 'REIMAGINE YOUR SPACE',
                'alt_text' => 'บ้านโมเดิร์นที่เลือกใช้วัสดุธรรมชาติ',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ...collect([
                ['/approach-homes/modern.jpg', 'Modern Residence', 'บ้านสไตล์โมเดิร์นเส้นสายเรียบคม'],
                ['/approach-homes/natural-modern.jpg', 'Natural Modern', 'บ้านโมเดิร์นที่ผสมวัสดุไม้ธรรมชาติ'],
                ['/approach-homes/contemporary.jpg', 'Modern Farmhouse', 'บ้านสไตล์โมเดิร์นฟาร์มเฮาส์'],
                ['/approach-homes/minimal.jpg', 'Rustic Contemporary', 'บ้านร่วมสมัยที่ใช้หินและวัสดุธรรมชาติ'],
                ['/approach-homes/natural.jpg', 'Nordic Bungalow', 'บ้านชั้นเดียวสไตล์นอร์ดิกท่ามกลางสวน'],
                ['/approach-homes/coastal-villa.jpg', 'Coastal Villa', 'บ้านพักตากอากาศสไตล์วิลลาริมน้ำ'],
                ['/approach-homes/classic.jpg', 'Resort Villa', 'บ้านพักสไตล์รีสอร์ตพร้อมพื้นที่สระว่ายน้ำ'],
                ['/approach-homes/villa.jpg', 'Classic Residence', 'บ้านพักอาศัยสไตล์คลาสสิกร่วมสมัย'],
            ])->map(fn (array $slide, int $index): array => [
                'section' => 'approach',
                'image_path' => $slide[0],
                'eyebrow' => null,
                'title' => $slide[1],
                'title_line_2' => null,
                'description' => null,
                'label' => null,
                'alt_text' => $slide[2],
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slides');
    }
};
