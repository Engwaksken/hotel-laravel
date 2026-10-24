<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: static function ($value): RoomStatus {
                if ($value instanceof RoomStatus) {
                    return $value;
                }

                return RoomStatus::tryFrom((string) $value)
                    ?? RoomStatus::Available;
            },
            set: static fn ($value) => $value instanceof RoomStatus
                ? $value->value
                : (RoomStatus::tryFrom((string) $value)?->value ?? RoomStatus::Available->value),
        );
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservationRooms()
    {
        return $this->hasMany(ReservationRoom::class);
    }
}
