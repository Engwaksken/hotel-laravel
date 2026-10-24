<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PurchaseReturnItem extends Model{protected $fillable=['purchase_return_id','inventory_item_id','quantity','unit_cost','reason']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
