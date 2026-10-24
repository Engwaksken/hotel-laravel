<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class SalesQuotationItem extends Model { protected $guarded=[]; protected function casts():array{return ['quantity'=>'decimal:3','unit_price'=>'decimal:2','tax_rate'=>'decimal:2','line_total'=>'decimal:2'];} public function quotation(){return $this->belongsTo(SalesQuotation::class,'sales_quotation_id');} }
