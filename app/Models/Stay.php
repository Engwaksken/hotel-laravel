<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Stay extends Model { protected $guarded=[]; protected function casts():array{return ['checked_in_at'=>'datetime','expected_check_out_at'=>'datetime','checked_out_at'=>'datetime'];} public function property(){return $this->belongsTo(Property::class);} public function reservation(){return $this->belongsTo(Reservation::class);} public function room(){return $this->belongsTo(Room::class);} public function guest(){return $this->belongsTo(Guest::class);} public function folio(){return $this->hasOne(Folio::class);} }
