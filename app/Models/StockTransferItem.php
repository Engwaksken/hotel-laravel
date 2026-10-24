<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StockTransferItem extends Model{protected $fillable=['stock_transfer_id','inventory_item_id','quantity_requested','quantity_dispatched','quantity_received']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
