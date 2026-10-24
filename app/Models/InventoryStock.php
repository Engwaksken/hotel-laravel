<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class InventoryStock extends Model{protected $fillable=['property_id','store_id','inventory_item_id','quantity','reserved_quantity']; public function store(){return $this->belongsTo(Store::class);} public function item(){return $this->belongsTo(InventoryItem::class,'inventory_item_id');}}
