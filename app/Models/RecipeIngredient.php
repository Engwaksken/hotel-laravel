<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class RecipeIngredient extends Model{protected $guarded=[];protected function casts():array{return['quantity'=>'decimal:4'];}public function recipe(){return $this->belongsTo(Recipe::class);}public function inventoryItem(){return $this->belongsTo(InventoryItem::class);}}
