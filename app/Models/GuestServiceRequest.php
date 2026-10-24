<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class GuestServiceRequest extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return['requested_at'=>'datetime','due_at'=>'datetime','completed_at'=>'datetime','charge_amount'=>'decimal:2','charge_posted'=>'boolean'];} public function room(){return $this->belongsTo(Room::class);} public function stay(){return $this->belongsTo(Stay::class);} public function guest(){return $this->belongsTo(Guest::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
