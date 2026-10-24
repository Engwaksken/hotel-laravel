<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Supplier extends Model{protected $fillable=['property_id','name','contact_person','phone','email','tin','address','bank_name','bank_account','credit_days','is_active'];}
