<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StockTransfer extends Model{protected $fillable=['property_id','from_store_id','to_store_id','number','status','notes','requested_by','approved_by','received_by','dispatched_at','received_at']; protected $casts=['dispatched_at'=>'datetime','received_at'=>'datetime']; public function items(){return $this->hasMany(StockTransferItem::class);} public function fromStore(){return $this->belongsTo(Store::class,'from_store_id');} public function toStore(){return $this->belongsTo(Store::class,'to_store_id');}}
