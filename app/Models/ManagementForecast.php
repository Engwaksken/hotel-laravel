<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class ManagementForecast extends Model{protected $guarded=[];protected function casts():array{return ['period_start'=>'date','period_end'=>'date','assumptions'=>'array'];}}
