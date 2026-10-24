<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('system_health_snapshots', function (Blueprint $t) {
            $t->id(); $t->string('status',20)->default('healthy'); $t->unsignedInteger('queue_pending')->default(0);
            $t->unsignedInteger('queue_failed')->default(0); $t->unsignedInteger('failed_logins_24h')->default(0);
            $t->unsignedBigInteger('database_size_bytes')->nullable(); $t->unsignedBigInteger('storage_free_bytes')->nullable();
            $t->json('checks')->nullable(); $t->timestamp('checked_at')->index(); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('system_health_snapshots'); }
};
