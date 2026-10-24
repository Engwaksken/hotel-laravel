<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LoyaltyTier extends Model {protected $guarded=[]; protected function casts():array{return ['benefits'=>'array','is_active'=>'boolean'];} public function accounts(){return $this->hasMany(LoyaltyAccount::class);} }
