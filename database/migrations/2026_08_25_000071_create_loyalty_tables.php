<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void {
Schema::create('loyalty_tiers',function(Blueprint $t){$t->id();$t->foreignId('property_id')->constrained()->cascadeOnDelete();$t->string('name');$t->unsignedInteger('minimum_points')->default(0);$t->decimal('discount_percent',5,2)->default(0);$t->decimal('points_multiplier',6,2)->default(1);$t->json('benefits')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['property_id','name']);});
Schema::create('loyalty_accounts',function(Blueprint $t){$t->id();$t->foreignId('property_id')->constrained()->cascadeOnDelete();$t->foreignId('guest_id')->constrained()->cascadeOnDelete();$t->foreignId('loyalty_tier_id')->nullable()->constrained()->nullOnDelete();$t->string('member_number')->unique();$t->unsignedBigInteger('points_balance')->default(0);$t->unsignedBigInteger('lifetime_points')->default(0);$t->date('joined_at')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['property_id','guest_id']);});
Schema::create('loyalty_transactions',function(Blueprint $t){$t->id();$t->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();$t->string('type',20);$t->bigInteger('points');$t->unsignedBigInteger('balance_after');$t->nullableMorphs('source');$t->string('description')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->index(['loyalty_account_id','created_at']);});
} public function down():void {Schema::dropIfExists('loyalty_transactions');Schema::dropIfExists('loyalty_accounts');Schema::dropIfExists('loyalty_tiers');}};
