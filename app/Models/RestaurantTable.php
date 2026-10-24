<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class RestaurantTable extends Model{protected $guarded=[];protected function casts():array{return['is_active'=>'boolean'];}public function property(){return $this->belongsTo(Property::class);}public function outlet(){return $this->belongsTo(Outlet::class);}public function orders(){return $this->hasMany(PosOrder::class);}}
