<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StockCountItem extends Model{protected $fillable=['stock_count_id','inventory_item_id','system_quantity','counted_quantity','variance_quantity','unit_cost','notes']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
