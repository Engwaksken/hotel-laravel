<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CorporateInvoiceItem extends Model { protected $guarded=[]; protected function casts():array{return ['quantity'=>'decimal:3','unit_price'=>'decimal:2','tax_amount'=>'decimal:2','line_total'=>'decimal:2'];} public function invoice(){return $this->belongsTo(CorporateInvoice::class,'corporate_invoice_id');} }
