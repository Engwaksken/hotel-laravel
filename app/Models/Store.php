<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Store extends Model{protected $fillable=['property_id','name','code','type','location','manager_id','is_active']; public function stocks(){return $this->hasMany(InventoryStock::class);}}
