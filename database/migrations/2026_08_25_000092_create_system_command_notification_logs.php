<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_command_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 60);
            $table->string('subject_type', 120);
            $table->unsignedBigInteger('subject_id');
            $table->string('event_key', 160);
            $table->dateTime('sent_at');
            $table->timestamps();

            $table->unique(
                ['user_id', 'event_type', 'subject_type', 'subject_id', 'event_key'],
                'system_command_notice_unique'
            );
            $table->index(['property_id', 'event_type', 'sent_at'], 'system_command_notice_property_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_command_notification_logs');
    }
};
