<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LoyaltyTransaction extends Model {protected $guarded=[]; public function account(){return $this->belongsTo(LoyaltyAccount::class,'loyalty_account_id');} public function source(){return $this->morphTo();}}
