<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class RoomType extends Model { protected $guarded=[]; protected function casts(): array{return ['amenities'=>'array','is_active'=>'boolean','base_rate'=>'decimal:2'];} public function property(){return $this->belongsTo(Property::class);} public function rooms(){return $this->hasMany(Room::class);} }