<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_designs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cover_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('cover_image')->nullable();
            $table->string('name', 160);
            $table->string('title', 255);
            $table->string('slug')->unique();
            $table->string('style', 32)->index();
            $table->string('budget_category', 32)->index();
            $table->string('budget_label', 120);
            $table->unsignedInteger('area');
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedTinyInteger('floors')->default(1);
            $table->unsignedTinyInteger('parking_spaces')->default(1);
            $table->text('description');
            $table->text('concept')->nullable();
            $table->json('features')->nullable();
            $table->string('cover_alt');
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('house_design_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('house_design_id')->constrained('house_designs')->cascadeOnDelete();
            $table->foreignId('stored_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->string('alt_text');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['house_design_id', 'sort_order']);
        });

        $this->seedExistingDesigns();
    }

    public function down(): void
    {
        Schema::dropIfExists('house_design_images');
        Schema::dropIfExists('house_designs');
    }

    private function seedExistingDesigns(): void
    {
        $now = now();
        $designs = [
            ['bm-courtyard', 'BM Courtyard', 'บ้านโมเดิร์นคอร์ทยาร์ด', 'modern', '5-10', '5.8 - 7.2 ล้านบาท', 285, 4, 4, 2, 2, '/approach-homes/modern.jpg'],
            ['nordic-retreat', 'Nordic Retreat', 'บ้านนอร์ดิกอบอุ่น', 'contemporary', 'under-5', '3.9 - 4.8 ล้านบาท', 210, 3, 3, 2, 2, '/approach-homes/natural.jpg'],
            ['warm-minimal', 'Warm Minimal', 'บ้านมินิมอลแสงธรรมชาติ', 'minimal', 'under-5', '3.4 - 4.3 ล้านบาท', 185, 3, 2, 2, 2, '/approach-homes/minimal.jpg'],
            ['urban-frame', 'Urban Frame', 'บ้านโมเดิร์นสำหรับครอบครัว', 'modern', '5-10', '6.5 - 8.2 ล้านบาท', 330, 4, 5, 2, 3, '/approach-homes/urban.jpg'],
            ['tropical-villa', 'Tropical Villa', 'พูลวิลล่าร่วมสมัย', 'contemporary', 'over-10', '10.8 - 13.5 ล้านบาท', 465, 5, 6, 2, 3, '/approach-homes/villa.jpg'],
            ['classic-residence', 'Classic Residence', 'บ้านคลาสสิกเหนือกาลเวลา', 'classic', 'over-10', '12.5 - 16 ล้านบาท', 520, 5, 6, 2, 4, '/approach-homes/classic.jpg'],
            ['coastal-living', 'Coastal Living', 'บ้านพักผ่อนรับวิว', 'contemporary', '5-10', '7.2 - 9.5 ล้านบาท', 350, 4, 4, 2, 3, '/approach-homes/coastal-villa.jpg'],
            ['natural-house', 'Natural House', 'บ้านธรรมชาติสมัยใหม่', 'minimal', '5-10', '5.1 - 6.4 ล้านบาท', 245, 3, 3, 2, 2, '/approach-homes/natural-modern.jpg'],
            ['modern-farmhouse', 'Modern Farmhouse', 'บ้านฟาร์มเฮาส์ร่วมสมัย', 'contemporary', '5-10', '5.6 - 7 ล้านบาท', 275, 4, 3, 2, 2, '/approach-homes/contemporary.jpg'],
            ['quiet-luxury', 'Quiet Luxury', 'บ้านหรูเส้นสายเรียบ', 'modern', 'over-10', '11 - 14.5 ล้านบาท', 490, 5, 6, 2, 4, '/approach-homes/warm-modern.jpg'],
        ];
        $galleryPool = [
            '/approach-homes/modern.jpg',
            '/approach-homes/natural-modern.jpg',
            '/approach-homes/contemporary.jpg',
            '/approach-homes/minimal.jpg',
            '/approach-homes/natural.jpg',
            '/approach-homes/coastal-villa.jpg',
            '/approach-homes/classic.jpg',
            '/approach-homes/villa.jpg',
            '/approach-homes/urban.jpg',
            '/approach-homes/warm-modern.jpg',
        ];

        foreach ($designs as $index => $design) {
            [$slug, $name, $title, $style, $budgetCategory, $budgetLabel, $area, $bedrooms, $bathrooms, $floors, $parking, $cover] = $design;
            $id = DB::table('house_designs')->insertGetId([
                'cover_image' => $cover,
                'name' => $name,
                'title' => $title,
                'slug' => $slug,
                'style' => $style,
                'budget_category' => $budgetCategory,
                'budget_label' => $budgetLabel,
                'area' => $area,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'floors' => $floors,
                'parking_spaces' => $parking,
                'description' => 'แบบบ้านที่ออกแบบให้สมดุลระหว่างภาพลักษณ์ ฟังก์ชัน และการใช้ชีวิตจริง สามารถปรับผังพื้นที่ วัสดุ และรายละเอียดให้เหมาะกับที่ดินและงบประมาณของแต่ละครอบครัว',
                'concept' => 'วางพื้นที่ส่วนกลางให้เชื่อมต่อกันอย่างเป็นธรรมชาติ รับแสงและลมได้ดี พร้อมแยกพื้นที่พักผ่อนส่วนตัวอย่างชัดเจน เพื่อให้บ้านใช้งานสะดวกในทุกช่วงเวลา',
                'features' => json_encode([
                    'พื้นที่ส่วนกลางโปร่งและเชื่อมต่อสวน',
                    'วางช่องเปิดเพื่อรับแสงธรรมชาติ',
                    'ปรับฟังก์ชันให้เหมาะกับขนาดครอบครัว',
                    'เลือกวัสดุและงบประมาณได้หลายระดับ',
                ], JSON_UNESCAPED_UNICODE),
                'cover_alt' => $title.' โดย 34 Build Master Construction',
                'status' => 'published',
                'sort_order' => ($index + 1) * 10,
                'seo_title' => $title.' | 34 Build Master Construction',
                'seo_description' => $title.' พื้นที่ใช้สอย '.$area.' ตารางเมตร '.$bedrooms.' ห้องนอน '.$bathrooms.' ห้องน้ำ ปรับแบบได้ตามพื้นที่และงบประมาณ',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (range(0, 2) as $galleryIndex) {
                $path = $galleryIndex === 0
                    ? $cover
                    : $galleryPool[($index + $galleryIndex) % count($galleryPool)];

                DB::table('house_design_images')->insert([
                    'house_design_id' => $id,
                    'image_path' => $path,
                    'alt_text' => $title.' มุมมองที่ '.($galleryIndex + 1),
                    'caption' => $galleryIndex === 0 ? 'ภาพรวมด้านหน้าแบบบ้าน' : null,
                    'sort_order' => ($galleryIndex + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
