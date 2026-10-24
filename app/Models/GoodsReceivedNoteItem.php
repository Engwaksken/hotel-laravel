<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GoodsReceivedNoteItem extends Model{protected $fillable=['goods_received_note_id','purchase_order_item_id','inventory_item_id','quantity_received','quantity_rejected','unit_cost','batch_number','expires_at','notes']; protected $casts=['expires_at'=>'date']; public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
