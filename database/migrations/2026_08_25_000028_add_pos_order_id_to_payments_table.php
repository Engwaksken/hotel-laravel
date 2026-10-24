<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::table('payments',function(Blueprint $t){$t->foreignId('pos_order_id')->nullable()->after('folio_id')->constrained()->nullOnDelete();});}public function down():void{Schema::table('payments',function(Blueprint $t){$t->dropConstrainedForeignId('pos_order_id');});}};
