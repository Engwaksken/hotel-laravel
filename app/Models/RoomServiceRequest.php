<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class RoomServiceRequest extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return['due_at'=>'datetime','delivered_at'=>'datetime'];} public function stay(){return $this->belongsTo(Stay::class);} public function guest(){return $this->belongsTo(Guest::class);} public function order(){return $this->belongsTo(PosOrder::class,'pos_order_id');} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
