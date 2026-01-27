<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('tagline');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('video_url')->nullable();
            $table->json('images');
            $table->json('colors');
            $table->json('sizes'); 
            $table->json('badges'); 
            $table->text('description');
            $table->json('tech_specs'); 
            $table->text('care_instructions');
            $table->json('shipping_info');
            $table->json('trust_badges'); 
            $table->json('size_guide'); 
            $table->string('designer_video_url')->nullable();
            $table->text('designer_notes')->nullable();
            $table->json('detail_images'); 
            $table->json('lifestyle_images'); 
            $table->json('related_products')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};