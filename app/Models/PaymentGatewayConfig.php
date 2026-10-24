<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PaymentGatewayConfig extends Model{protected $guarded=[];protected function casts():array{return ['enabled'=>'boolean','sandbox'=>'boolean','credentials'=>'encrypted:array','settings'=>'array','last_tested_at'=>'datetime'];}}
