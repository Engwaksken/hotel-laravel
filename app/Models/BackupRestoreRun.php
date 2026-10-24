<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class BackupRestoreRun extends Model{protected $guarded=[];protected function casts():array{return ['dry_run'=>'boolean','started_at'=>'datetime','finished_at'=>'datetime'];}public function backup(){return $this->belongsTo(BackupRun::class,'backup_run_id');}}
