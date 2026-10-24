<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class SalesQuotation extends Model { protected $guarded=[]; protected function casts():array{return ['quotation_date'=>'date','valid_until'=>'date','subtotal'=>'decimal:2','discount_total'=>'decimal:2','tax_total'=>'decimal:2','grand_total'=>'decimal:2'];} public function items(){return $this->hasMany(SalesQuotationItem::class);} public function company(){return $this->belongsTo(CorporateAccount::class,'corporate_account_id');} public function agent(){return $this->belongsTo(TravelAgent::class,'travel_agent_id');} }
