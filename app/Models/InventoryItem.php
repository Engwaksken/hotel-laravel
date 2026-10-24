<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class InventoryItem extends Model{protected $guarded=[];protected function casts():array{return['quantity_on_hand'=>'decimal:4','reorder_level'=>'decimal:4','average_cost'=>'decimal:2','track_stock'=>'boolean','is_active'=>'boolean'];}public function property(){return $this->belongsTo(Property::class);}public function movements(){return $this->hasMany(StockMovement::class);}public function recipeIngredients(){return $this->hasMany(RecipeIngredient::class);}}
