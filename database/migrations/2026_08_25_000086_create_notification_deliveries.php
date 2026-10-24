<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('notification_deliveries', function (Blueprint $t) {
            $t->id(); $t->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $t->string('channel',30); $t->string('destination')->nullable(); $t->string('subject')->nullable(); $t->text('body');
            $t->string('status',30)->default('queued')->index(); $t->string('provider')->nullable(); $t->string('provider_reference')->nullable();
            $t->text('error_message')->nullable(); $t->timestamp('sent_at')->nullable(); $t->timestamp('failed_at')->nullable(); $t->timestamps();
            $t->index(['property_id','channel','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('notification_deliveries'); }
};
