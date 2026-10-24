<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class HousekeepingTask extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return['due_at'=>'datetime','started_at'=>'datetime','completed_at'=>'datetime','inspected_at'=>'datetime'];} public function room(){return $this->belongsTo(Room::class);} public function stay(){return $this->belongsTo(Stay::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
