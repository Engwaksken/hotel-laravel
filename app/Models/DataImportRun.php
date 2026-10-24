<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class DataImportRun extends Model{protected $guarded=[];protected function casts():array{return ['errors'=>'array'];}}
