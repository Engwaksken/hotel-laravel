<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class SystemAuditEvent extends Model{public $timestamps=false;protected $guarded=[];protected function casts():array{return ['before_values'=>'array','after_values'=>'array','created_at'=>'datetime'];}}
