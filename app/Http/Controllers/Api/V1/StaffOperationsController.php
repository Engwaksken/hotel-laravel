<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;use App\Models\{HousekeepingTask,MaintenanceRequest,GuestServiceRequest,KitchenTicket};use Illuminate\Http\Request;
class StaffOperationsController extends Controller{
 private function employee(Request $r){return $r->user()->employee ?: abort(403,'No employee profile is linked to this account.');}
 public function tasks(Request $r){$e=$this->employee($r);$pid=$r->user()->property_id;return response()->json(['housekeeping'=>HousekeepingTask::where('property_id',$pid)->where('assigned_to',$r->user()->id)->whereNotIn('status',['completed','inspected'])->latest()->limit(30)->get(),'maintenance'=>MaintenanceRequest::where('property_id',$pid)->where('assigned_to',$r->user()->id)->whereNotIn('status',['resolved','closed'])->latest()->limit(30)->get(),'guest_services'=>GuestServiceRequest::where('property_id',$pid)->where('assigned_to',$r->user()->id)->whereNotIn('status',['completed','cancelled'])->latest()->limit(30)->get()]);}
 public function kitchen(Request $r){return response()->json(['data'=>KitchenTicket::where('property_id',$r->user()->property_id)->whereNotIn('status',['served','cancelled'])->oldest()->limit(50)->get()]);}
}
