<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class NotificationDelivery extends Model{protected $guarded=[];protected function casts():array{return ['sent_at'=>'datetime','failed_at'=>'datetime'];}}
