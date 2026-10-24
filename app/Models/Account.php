<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class Account extends Model{protected $guarded=[];protected function casts():array{return['is_control'=>'boolean','is_active'=>'boolean'];}public function property(){return $this->belongsTo(Property::class);}public function parent(){return $this->belongsTo(self::class,'parent_id');}public function lines(){return $this->hasMany(JournalLine::class);}}
