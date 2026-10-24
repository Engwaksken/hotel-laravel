<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class AgentCommission extends Model { protected $guarded=[]; protected function casts():array{return ['base_amount'=>'decimal:2','commission_percent'=>'decimal:2','commission_amount'=>'decimal:2','paid_at'=>'date'];} public function agent(){return $this->belongsTo(TravelAgent::class,'travel_agent_id');} }
