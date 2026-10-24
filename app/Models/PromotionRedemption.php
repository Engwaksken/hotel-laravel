<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class PromotionRedemption extends Model {protected $guarded=[]; public function promotion(){return $this->belongsTo(Promotion::class);} public function redeemable(){return $this->morphTo();} }
