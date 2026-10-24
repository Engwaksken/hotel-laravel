<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PurchaseReturn extends Model{protected $fillable=['property_id','supplier_id','store_id','goods_received_note_id','number','return_date','status','reason','created_by','posted_at']; protected $casts=['return_date'=>'date','posted_at'=>'datetime']; public function items(){return $this->hasMany(PurchaseReturnItem::class);} public function supplier(){return $this->belongsTo(Supplier::class);} public function store(){return $this->belongsTo(Store::class);}}
