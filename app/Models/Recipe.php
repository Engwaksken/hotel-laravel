<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Recipe extends Model{protected $guarded=[];protected function casts():array{return['yield_quantity'=>'decimal:4'];}public function menuItem(){return $this->belongsTo(MenuItem::class);}public function ingredients(){return $this->hasMany(RecipeIngredient::class);}}
