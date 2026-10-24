<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Dirty = 'dirty';
    case Cleaning = 'cleaning';
    case Inspected = 'inspected';
    case Maintenance = 'maintenance';
    case Blocked = 'blocked';
    case OutOfOrder = 'out_of_order';
}
