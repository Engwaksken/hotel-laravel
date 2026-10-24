<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class JournalEntry extends Model{protected $guarded=[];protected function casts():array{return['entry_date'=>'date','business_date'=>'date'];}public function lines(){return $this->hasMany(JournalLine::class);}public function property(){return $this->belongsTo(Property::class);}public function creator(){return $this->belongsTo(User::class,'created_by');}}
