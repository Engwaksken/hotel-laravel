<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('homepage_banners')) {
            Schema::create('homepage_banners', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('property_id')->nullable()->index();
                $table->string('title', 180);
                $table->string('subtitle', 255)->nullable();
                $table->string('image_path', 500);
                $table->string('button_text', 80)->nullable();
                $table->string('button_url', 500)->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hotel_services')) {
            Schema::create('hotel_services', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('property_id')->nullable()->index();
                $table->string('name', 160);
                $table->string('slug', 190)->unique();
                $table->string('short_description', 300)->nullable();
                $table->text('description')->nullable();
                $table->string('image_path', 500)->nullable();
                $table->decimal('price', 14, 2)->nullable();
                $table->string('price_label', 80)->nullable();
                $table->string('button_text', 80)->nullable();
                $table->string('button_url', 500)->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('show_on_homepage')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_services');
        Schema::dropIfExists('homepage_banners');
    }
};
