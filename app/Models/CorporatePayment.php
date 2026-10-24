<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CorporatePayment extends Model { protected $guarded=[]; protected function casts():array{return ['payment_date'=>'date','amount'=>'decimal:2'];} public function invoice(){return $this->belongsTo(CorporateInvoice::class,'corporate_invoice_id');} }
