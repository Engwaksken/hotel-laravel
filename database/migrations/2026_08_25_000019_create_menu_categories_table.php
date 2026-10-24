<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('menu_categories',function(Blueprint $t){$t->id();$t->foreignId('property_id')->constrained()->cascadeOnDelete();$t->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();$t->string('name');$t->unsignedInteger('display_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});}public function down():void{Schema::dropIfExists('menu_categories');}};
