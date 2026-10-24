<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class HotelEvent extends Model { protected $guarded=[]; protected function casts():array{return ['starts_at'=>'datetime','ends_at'=>'datetime','estimated_total'=>'decimal:2','deposit_required'=>'decimal:2'];} public function venue(){return $this->belongsTo(EventVenue::class,'event_venue_id');} public function company(){return $this->belongsTo(CorporateAccount::class,'corporate_account_id');} public function agent(){return $this->belongsTo(TravelAgent::class,'travel_agent_id');} }
