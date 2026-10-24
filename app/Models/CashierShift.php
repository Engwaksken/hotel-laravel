<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CashierShift extends Model { protected $guarded=[]; protected function casts():array{return ['opening_cash'=>'decimal:2','expected_cash'=>'decimal:2','closing_cash'=>'decimal:2','variance'=>'decimal:2','opened_at'=>'datetime','closed_at'=>'datetime'];} public function property(){return $this->belongsTo(Property::class);} public function user(){return $this->belongsTo(User::class);} }
