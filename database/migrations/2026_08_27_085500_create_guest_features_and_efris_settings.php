<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('guest_comments')) {
            Schema::create('guest_comments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('property_id')->nullable()->index();
                $table->string('guest_name', 160);
                $table->string('email', 190)->nullable();
                $table->unsignedTinyInteger('rating')->default(5);
                $table->text('comment');
                $table->enum('status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->index();
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hotel_feature_settings')) {
            Schema::create('hotel_feature_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 190)->unique();
                $table->longText('value')->nullable();
                $table->boolean('is_secret')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('chatbot_faqs')) {
            Schema::create('chatbot_faqs', function (Blueprint $table): void {
                $table->id();
                $table->string('question', 255);
                $table->text('answer');
                $table->text('keywords')->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_faqs');
        Schema::dropIfExists('hotel_feature_settings');
        Schema::dropIfExists('guest_comments');
    }
};
