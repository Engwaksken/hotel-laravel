<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GoodsReceivedNote extends Model{protected $fillable=['property_id','purchase_order_id','store_id','number','received_date','delivery_note_number','status','received_by','notes','posted_at']; protected $casts=['received_date'=>'date','posted_at'=>'datetime']; public function items(){return $this->hasMany(GoodsReceivedNoteItem::class);} public function order(){return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');} public function store(){return $this->belongsTo(Store::class);}}
