<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class GuestNote extends Model {protected $guarded=[]; protected function casts():array{return ['is_private'=>'boolean'];} public function guest(){return $this->belongsTo(Guest::class);} }
