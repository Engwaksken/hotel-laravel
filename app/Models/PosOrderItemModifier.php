<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PosOrderItemModifier extends Model{protected $guarded=[];protected function casts():array{return['price_delta'=>'decimal:2'];}public function item(){return $this->belongsTo(PosOrderItem::class,'pos_order_item_id');}}
