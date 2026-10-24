<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PurchaseRequisition extends Model{protected $fillable=['property_id','store_id','number','department','priority','status','reason','requested_by','approved_by','submitted_at','approved_at']; protected $casts=['submitted_at'=>'datetime','approved_at'=>'datetime']; public function items(){return $this->hasMany(PurchaseRequisitionItem::class);} public function store(){return $this->belongsTo(Store::class);}}
