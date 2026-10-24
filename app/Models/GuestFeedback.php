<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class GuestFeedback extends Model {protected $table='guest_feedback'; protected $guarded=[]; protected function casts():array{return ['submitted_at'=>'datetime','permission_to_publish'=>'boolean'];} public function guest(){return $this->belongsTo(Guest::class);} }
