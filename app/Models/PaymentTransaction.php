<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PaymentTransaction extends Model { protected $guarded=[]; protected function casts():array{return ['request_payload'=>'array','response_payload'=>'array','webhook_payload'=>'array','verified_at'=>'datetime','reconciled_at'=>'datetime'];} public function payment(){return $this->belongsTo(Payment::class);} }