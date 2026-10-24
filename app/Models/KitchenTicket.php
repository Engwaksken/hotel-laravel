<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class KitchenTicket extends Model{protected $guarded=[];protected function casts():array{return['accepted_at'=>'datetime','ready_at'=>'datetime','served_at'=>'datetime'];}public function order(){return $this->belongsTo(PosOrder::class,'pos_order_id');}public function outlet(){return $this->belongsTo(Outlet::class);}}
