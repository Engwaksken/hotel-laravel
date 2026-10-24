<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GuestNotification extends Model { protected $guarded=[]; protected function casts():array{return ['data'=>'array','read_at'=>'datetime'];} }
