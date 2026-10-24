<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::table('outlets',function(Blueprint $t){$t->foreignId('store_id')->nullable()->after('property_id')->constrained('stores')->nullOnDelete();});}public function down():void{Schema::table('outlets',function(Blueprint $t){$t->dropConstrainedForeignId('store_id');});}};
