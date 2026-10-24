<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::table('reservations',function(Blueprint $t){$t->timestamp('cancelled_at')->nullable();$t->text('cancellation_reason')->nullable();$t->unsignedSmallInteger('reschedule_count')->default(0);});}public function down():void{Schema::table('reservations',fn(Blueprint $t)=>$t->dropColumn(['cancelled_at','cancellation_reason','reschedule_count']));}};
