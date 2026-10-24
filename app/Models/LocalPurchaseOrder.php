<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LocalPurchaseOrder extends Model { protected $guarded=[]; protected function casts():array{return ['lpo_date'=>'date','authorised_amount'=>'decimal:2','used_amount'=>'decimal:2'];} public function company(){return $this->belongsTo(CorporateAccount::class,'corporate_account_id');} }
