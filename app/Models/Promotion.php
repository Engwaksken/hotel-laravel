<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class Promotion extends Model {protected $guarded=[]; protected function casts():array{return ['starts_at'=>'datetime','ends_at'=>'datetime','applies_to'=>'array','is_active'=>'boolean'];} public function redemptions(){return $this->hasMany(PromotionRedemption::class);} }
