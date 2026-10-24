<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'status')) {
            DB::table('rooms')
                ->where('status', 'clean')
                ->update(['status' => 'available']);
        }
    }

    public function down(): void
    {
        // The old "clean" value was invalid as an operational room status.
    }
};
