<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class NightAudit extends Model{protected $guarded=[];protected function casts():array{return['business_date'=>'date','exceptions'=>'array','started_at'=>'datetime','completed_at'=>'datetime'];}}
