<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StockCount extends Model{protected $fillable=['property_id','store_id','number','count_date','status','notes','created_by','approved_by','posted_at']; protected $casts=['count_date'=>'date','posted_at'=>'datetime']; public function items(){return $this->hasMany(StockCountItem::class);} public function store(){return $this->belongsTo(Store::class);}}
