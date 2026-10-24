<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class LaundryService extends Model{protected $guarded=[];protected function casts():array{return['guest_price'=>'decimal:2','internal_cost'=>'decimal:2','is_active'=>'boolean'];}}
