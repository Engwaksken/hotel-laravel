<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('backup_runs', function (Blueprint $t) {
            $t->string('checksum',64)->nullable()->after('path');
            $t->timestamp('verified_at')->nullable()->after('finished_at');
        });
    }
    public function down(): void {
        Schema::table('backup_runs', fn(Blueprint $t) => $t->dropColumn(['checksum','verified_at']));
    }
};
