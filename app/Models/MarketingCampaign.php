<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class MarketingCampaign extends Model {protected $guarded=[]; protected function casts():array{return ['audience_filters'=>'array','scheduled_at'=>'datetime','sent_at'=>'datetime'];} public function messages(){return $this->hasMany(MarketingMessage::class);} }
