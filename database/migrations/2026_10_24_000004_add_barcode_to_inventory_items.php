<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_items') || Schema::hasColumn('inventory_items', 'barcode')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->string('barcode', 100)->nullable()->after('sku');
            $table->unique(['property_id', 'barcode'], 'inventory_property_barcode_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_items') || ! Schema::hasColumn('inventory_items', 'barcode')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropUnique('inventory_property_barcode_unique');
            $table->dropColumn('barcode');
        });
    }
};
