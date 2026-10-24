<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('pos_order_item_modifiers',function(Blueprint $t){$t->id();$t->foreignId('pos_order_item_id')->constrained()->cascadeOnDelete();$t->foreignId('menu_modifier_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->decimal('price_delta',14,2)->default(0);$t->timestamps();});}public function down():void{Schema::dropIfExists('pos_order_item_modifiers');}};
