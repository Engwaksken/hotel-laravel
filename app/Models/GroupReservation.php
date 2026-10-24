<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GroupReservation extends Model { protected $guarded=[]; protected function casts():array{return ['check_in_at'=>'datetime','check_out_at'=>'datetime','estimated_total'=>'decimal:2','deposit_required'=>'decimal:2'];} public function reservations(){return $this->hasMany(Reservation::class);} public function company(){return $this->belongsTo(CorporateAccount::class,'corporate_account_id');} public function agent(){return $this->belongsTo(TravelAgent::class,'travel_agent_id');} }
