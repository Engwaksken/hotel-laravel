<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class ReceivableTransaction extends Model{protected $guarded=[];protected function casts():array{return['transaction_date'=>'date','due_date'=>'date','debit'=>'decimal:2','credit'=>'decimal:2'];}public function account(){return $this->belongsTo(ReceivableAccount::class,'receivable_account_id');}}
