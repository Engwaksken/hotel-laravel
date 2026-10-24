<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class ReceivableAccount extends Model{protected $guarded=[];protected function casts():array{return['credit_limit'=>'decimal:2','balance'=>'decimal:2','active'=>'boolean'];}public function transactions(){return $this->hasMany(ReceivableTransaction::class);}}
