<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class DigitalCheckin extends Model { protected $guarded=[]; protected function casts():array{return ['terms_accepted'=>'boolean','submitted_at'=>'datetime','reviewed_at'=>'datetime'];} public function reservation(){return $this->belongsTo(Reservation::class);} public function guest(){return $this->belongsTo(Guest::class);} }
