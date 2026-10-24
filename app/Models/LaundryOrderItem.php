<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class LaundryOrderItem extends Model{protected $guarded=[];protected function casts():array{return['quantity'=>'decimal:2','unit_price'=>'decimal:2','total'=>'decimal:2'];}public function order(){return $this->belongsTo(LaundryOrder::class,'laundry_order_id');}public function service(){return $this->belongsTo(LaundryService::class,'laundry_service_id');}}
