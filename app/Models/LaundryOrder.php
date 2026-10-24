<?php
namespace App\Models;
use App\Models\Concerns\NotifiesTaskAssignee;
use Illuminate\Database\Eloquent\Model;
class LaundryOrder extends Model { use NotifiesTaskAssignee; protected $guarded=[]; protected function casts():array{return['received_at'=>'datetime','due_at'=>'datetime','delivered_at'=>'datetime','total'=>'decimal:2','posted_to_folio'=>'boolean'];} public function items(){return $this->hasMany(LaundryOrderItem::class);} public function room(){return $this->belongsTo(Room::class);} public function stay(){return $this->belongsTo(Stay::class);} public function guest(){return $this->belongsTo(Guest::class);} public function folio(){return $this->belongsTo(Folio::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
