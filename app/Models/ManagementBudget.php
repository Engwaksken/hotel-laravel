<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class ManagementBudget extends Model{protected $guarded=[];public function lines(){return $this->hasMany(ManagementBudgetLine::class);}public function property(){return $this->belongsTo(Property::class);}}
