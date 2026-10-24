<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class HotelBusinessDate extends Model{protected $guarded=[];protected $table='hotel_business_dates';protected function casts():array{return['business_date'=>'date','opened_at'=>'datetime','closed_at'=>'datetime'];}}
