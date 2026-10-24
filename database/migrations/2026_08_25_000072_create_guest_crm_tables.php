<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void {
Schema::create('guest_preferences',function(Blueprint $t){$t->id();$t->foreignId('guest_id')->constrained()->cascadeOnDelete();$t->string('category',60);$t->string('preference');$t->text('notes')->nullable();$t->boolean('is_sensitive')->default(false);$t->timestamps();$t->index(['guest_id','category']);});
Schema::create('guest_notes',function(Blueprint $t){$t->id();$t->foreignId('property_id')->constrained()->cascadeOnDelete();$t->foreignId('guest_id')->constrained()->cascadeOnDelete();$t->string('type',40)->default('general');$t->text('note');$t->boolean('is_private')->default(false);$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
Schema::create('guest_tags',function(Blueprint $t){$t->id();$t->foreignId('property_id')->constrained()->cascadeOnDelete();$t->string('name');$t->string('colour',20)->nullable();$t->timestamps();$t->unique(['property_id','name']);});
Schema::create('guest_guest_tag',function(Blueprint $t){$t->foreignId('guest_id')->constrained()->cascadeOnDelete();$t->foreignId('guest_tag_id')->constrained()->cascadeOnDelete();$t->primary(['guest_id','guest_tag_id']);});
} public function down():void {Schema::dropIfExists('guest_guest_tag');Schema::dropIfExists('guest_tags');Schema::dropIfExists('guest_notes');Schema::dropIfExists('guest_preferences');}};
