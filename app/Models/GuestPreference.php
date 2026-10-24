<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class GuestPreference extends Model {protected $guarded=[]; protected function casts():array{return ['is_sensitive'=>'boolean'];} public function guest(){return $this->belongsTo(Guest::class);} }
