<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class InventoryBatch extends Model{protected $fillable=['property_id','store_id','inventory_item_id','batch_number','manufactured_at','expires_at','quantity_received','quantity_remaining','unit_cost','supplier_id']; protected $casts=['manufactured_at'=>'date','expires_at'=>'date']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);} public function store(){return $this->belongsTo(Store::class);} public function supplier(){return $this->belongsTo(Supplier::class);}}
