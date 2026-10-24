<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class MenuModifier extends Model{protected $guarded=[];protected function casts():array{return['price_delta'=>'decimal:2','is_active'=>'boolean'];}public function items(){return $this->belongsToMany(MenuItem::class,'menu_item_modifier');}}
