<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class GuestComplaint extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return ['due_at'=>'datetime','resolved_at'=>'datetime'];} public function guest(){return $this->belongsTo(Guest::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
