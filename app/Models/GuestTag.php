<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class GuestTag extends Model {protected $guarded=[]; public function guests(){return $this->belongsToMany(Guest::class);} }
