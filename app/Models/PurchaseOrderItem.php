<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PurchaseOrderItem extends Model{protected $fillable=['purchase_order_id','inventory_item_id','quantity','received_quantity','unit_cost','tax_rate','line_total','notes']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
