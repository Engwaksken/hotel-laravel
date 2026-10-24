<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class ManagementBudgetLine extends Model{protected $guarded=[];public function budget(){return $this->belongsTo(ManagementBudget::class,'management_budget_id');}public function account(){return $this->belongsTo(Account::class);}}
