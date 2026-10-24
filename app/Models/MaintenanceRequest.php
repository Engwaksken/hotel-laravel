<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class MaintenanceRequest extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return['blocks_room'=>'boolean','due_at'=>'datetime','started_at'=>'datetime','resolved_at'=>'datetime','closed_at'=>'datetime','estimated_cost'=>'decimal:2','actual_cost'=>'decimal:2'];} public function room(){return $this->belongsTo(Room::class);} public function stay(){return $this->belongsTo(Stay::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
