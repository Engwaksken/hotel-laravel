<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class OnboardingState extends Model{protected $guarded=[];protected function casts():array{return ['completed'=>'boolean','completed_at'=>'datetime','meta'=>'array'];}}
