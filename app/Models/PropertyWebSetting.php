<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PropertyWebSetting extends Model { protected $guarded=[]; protected function casts():array{return ['social_links'=>'array','online_booking_enabled'=>'boolean','digital_checkin_enabled'=>'boolean','default_deposit_percent'=>'decimal:2'];} public function property(){return $this->belongsTo(Property::class);} }
