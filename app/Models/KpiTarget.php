<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class KpiTarget extends Model{protected $guarded=[];protected function casts():array{return ['period_start'=>'date','period_end'=>'date'];}}
