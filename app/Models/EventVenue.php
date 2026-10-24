<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class EventVenue extends Model { protected $guarded=[]; protected function casts():array{return ['base_rate'=>'decimal:2'];} public function events(){return $this->hasMany(HotelEvent::class);} }
