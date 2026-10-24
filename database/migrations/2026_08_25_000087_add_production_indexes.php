<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('reservations', fn(Blueprint $t) => $t->index(['property_id','status','check_in_at','check_out_at'], 'reservations_property_status_dates_idx'));
        Schema::table('payments', fn(Blueprint $t) => $t->index(['property_id','status','created_at'], 'payments_property_status_created_idx'));
        Schema::table('pos_orders', fn(Blueprint $t) => $t->index(['property_id','status','created_at'], 'pos_orders_property_status_created_idx'));
        Schema::table('inventory_items', fn(Blueprint $t) => $t->index(['property_id','is_active'], 'inventory_property_active_idx'));
    }
    public function down(): void {
        Schema::table('reservations', fn(Blueprint $t) => $t->dropIndex('reservations_property_status_dates_idx'));
        Schema::table('payments', fn(Blueprint $t) => $t->dropIndex('payments_property_status_created_idx'));
        Schema::table('pos_orders', fn(Blueprint $t) => $t->dropIndex('pos_orders_property_status_created_idx'));
        Schema::table('inventory_items', fn(Blueprint $t) => $t->dropIndex('inventory_property_active_idx'));
    }
};
