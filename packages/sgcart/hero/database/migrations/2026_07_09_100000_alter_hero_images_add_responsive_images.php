<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hero_images', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
            $table->string('image_desktop')->nullable();
            $table->string('image_tablet')->nullable();
            $table->string('image_mobile')->nullable();
        });

        // Copy existing image_path values to image_desktop
        try {
            DB::table('hero_images')->whereNotNull('image_path')->update([
                'image_desktop' => DB::raw('image_path')
            ]);
        } catch (\Exception $e) {
            // Silently ignore if update fails
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_images', function (Blueprint $table) {
            $table->string('image_path')->nullable(false)->change();
            $table->dropColumn(['image_desktop', 'image_tablet', 'image_mobile']);
        });
    }
};
