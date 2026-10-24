<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class FinancialAccount extends Model{protected $guarded=[];protected function casts():array{return['opening_balance'=>'decimal:2','active'=>'boolean'];}public function ledgerAccount(){return $this->belongsTo(Account::class,'account_id');}public function reconciliations(){return $this->hasMany(BankReconciliation::class);}}
