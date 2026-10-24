<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model{protected $guarded=[];protected function casts():array{return['quantity'=>'decimal:4','unit_cost'=>'decimal:2','balance_after'=>'decimal:4'];}public function item(){return $this->belongsTo(InventoryItem::class,'inventory_item_id');}public function inventoryItem(){return $this->item();}public function source(){return $this->morphTo();}}
