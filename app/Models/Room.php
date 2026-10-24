<?php
namespace App\Models; use App\Enums\RoomStatus; use Illuminate\Database\Eloquent\Model;
class Room extends Model { protected $guarded=[]; protected function casts():array{return ['status'=>RoomStatus::class,'is_active'=>'boolean'];} public function property(){return $this->belongsTo(Property::class);} public function roomType(){return $this->belongsTo(RoomType::class);} public function reservationRooms(){return $this->hasMany(ReservationRoom::class);} }
