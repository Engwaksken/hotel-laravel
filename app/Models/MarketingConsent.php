<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class MarketingConsent extends Model {protected $guarded=[]; protected function casts():array{return ['consented'=>'boolean','consented_at'=>'datetime','withdrawn_at'=>'datetime'];} }
