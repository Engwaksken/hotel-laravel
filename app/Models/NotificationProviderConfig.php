<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class NotificationProviderConfig extends Model{protected $guarded=[];protected function casts():array{return ['enabled'=>'boolean','credentials'=>'encrypted:array','settings'=>'array'];}}
