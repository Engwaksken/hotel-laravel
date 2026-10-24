<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LoyaltyAccount extends Model {protected $guarded=[]; protected function casts():array{return ['joined_at'=>'date','is_active'=>'boolean'];} public function guest(){return $this->belongsTo(Guest::class);} public function tier(){return $this->belongsTo(LoyaltyTier::class,'loyalty_tier_id');} public function transactions(){return $this->hasMany(LoyaltyTransaction::class);} }
