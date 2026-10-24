<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PurchaseRequisitionItem extends Model{protected $fillable=['purchase_requisition_id','inventory_item_id','quantity','estimated_unit_cost','notes']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
