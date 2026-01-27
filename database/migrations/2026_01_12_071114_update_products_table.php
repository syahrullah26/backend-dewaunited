<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (Schema::hasColumn('products', 'tagline')) {
                $table->renameColumn('tagline', 'category');
            }

            if (Schema::hasColumn('products', 'designer_notes')) {
                $table->renameColumn('designer_notes', 'detail_product');
            }

            if (Schema::hasColumn('products', 'designer_video_url')) {
                $table->renameColumn('designer_video_url', 'description_video_url');
            }

            if (Schema::hasColumn('products', 'care_instructions')) {
                $table->renameColumn('care_instructions', 'size_guide_desc');
            }

            if (Schema::hasColumn('products', 'tech_specs')) {
                $table->dropColumn('tech_specs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (Schema::hasColumn('products', 'category')) {
                $table->renameColumn('category', 'tagline');
            }

            if (Schema::hasColumn('products', 'detail_product')) {
                $table->renameColumn('detail_product', 'designer_notes');
            }

            if (Schema::hasColumn('products', 'description_video_url')) {
                $table->renameColumn('description_video_url', 'designer_video_url');
            }

            if (Schema::hasColumn('products', 'size_guide_desc')) {
                $table->renameColumn('size_guide_desc', 'care_instructions');
            }

            if (!Schema::hasColumn('products', 'tech_specs')) {
                $table->json('tech_specs')->nullable();
            }
        });
    }
};