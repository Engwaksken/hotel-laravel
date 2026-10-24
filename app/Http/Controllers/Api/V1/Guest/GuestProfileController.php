<?php
namespace App\Http\Controllers\Api\V1\Guest;
use App\Http\Controllers\Controller; use Illuminate\Http\Request;
class GuestProfileController extends Controller{public function show(Request $r){return $r->user()->load('guest');}public function update(Request $r){$u=$r->user();abort_unless($u->role==='guest'&&$u->guest_id,403);$d=$r->validate(['first_name'=>'required|string|max:100','last_name'=>'nullable|string|max:100','phone'=>'nullable|string|max:50','nationality'=>'nullable|string|max:100','address'=>'nullable|string|max:1000','vehicle_registration'=>'nullable|string|max:50']);$u->guest->update($d);$u->update(['name'=>trim($d['first_name'].' '.($d['last_name']??'')),'phone'=>$d['phone']??$u->phone]);return $u->fresh()->load('guest');}}
