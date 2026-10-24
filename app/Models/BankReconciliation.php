<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class BankReconciliation extends Model{protected $guarded=[];protected function casts():array{return['statement_date'=>'date','statement_balance'=>'decimal:2','book_balance'=>'decimal:2','difference'=>'decimal:2','reconciled_at'=>'datetime'];}public function financialAccount(){return $this->belongsTo(FinancialAccount::class);}}
