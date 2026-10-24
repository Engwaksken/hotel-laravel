<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class LostFoundItem extends Model{protected $guarded=[];protected function casts():array{return['found_at'=>'datetime','returned_at'=>'datetime'];}public function room(){return $this->belongsTo(Room::class);}public function stay(){return $this->belongsTo(Stay::class);}}
