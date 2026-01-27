<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upload_storage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->enum('type',['image','video'])->default('image');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();
            
            
        });
        Schema::table('upload_storage', function (Blueprint $table){
            $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
