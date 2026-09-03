<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('home_slides')
            ->where('image_path', '/hero-construction.png')
            ->update(['image_path' => '/hero-construction.webp']);
    }

    public function down(): void
    {
        DB::table('home_slides')
            ->where('image_path', '/hero-construction.webp')
            ->update(['image_path' => '/hero-construction.png']);
    }
};
