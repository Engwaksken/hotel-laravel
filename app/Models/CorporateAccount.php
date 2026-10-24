<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CorporateAccount extends Model { protected $guarded=[]; protected function casts():array{return ['credit_limit'=>'decimal:2','default_discount_percent'=>'decimal:2'];} public function reservations(){return $this->hasMany(Reservation::class);} public function invoices(){return $this->hasMany(CorporateInvoice::class);} public function lpos(){return $this->hasMany(LocalPurchaseOrder::class);} }
