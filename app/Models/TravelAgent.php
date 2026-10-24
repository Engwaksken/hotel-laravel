<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class TravelAgent extends Model { protected $guarded=[]; protected function casts():array{return ['commission_percent'=>'decimal:2','credit_limit'=>'decimal:2'];} public function reservations(){return $this->hasMany(Reservation::class);} public function commissions(){return $this->hasMany(AgentCommission::class);} }
