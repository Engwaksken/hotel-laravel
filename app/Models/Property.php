<?php
namespace App\Models; use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Property extends Model { use HasFactory, SoftDeletes; protected $guarded=[]; protected function casts(): array { return ['is_active'=>'boolean','settings'=>'array']; } public function rooms(){return $this->hasMany(Room::class);} public function roomTypes(){return $this->hasMany(RoomType::class);} public function reservations(){return $this->hasMany(Reservation::class);} }
