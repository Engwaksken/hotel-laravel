<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class MinibarRoomItem extends Model{protected $guarded=[];protected function casts():array{return['par_quantity'=>'decimal:3','selling_price'=>'decimal:2','is_active'=>'boolean'];}public function room(){return $this->belongsTo(Room::class);}public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}public function store(){return $this->belongsTo(Store::class);}}
