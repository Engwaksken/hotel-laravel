<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;use App\Models\{AttendanceRecord,LeaveRequest,LeaveType,StaffShiftAssignment};use Illuminate\Http\Request;
class HrStaffController extends Controller{
 private function employee(Request $r){return $r->user()->employee()->firstOrFail();}
 public function me(Request $r){$e=$this->employee($r);return response()->json($e->load(['department','position']));}
 public function shifts(Request $r){$e=$this->employee($r);return response()->json(StaffShiftAssignment::with('shift')->where('employee_id',$e->id)->whereDate('shift_date','>=',today()->subDays(7))->orderBy('shift_date')->get());}
 public function attendance(Request $r){$e=$this->employee($r);return response()->json(AttendanceRecord::where('employee_id',$e->id)->latest('attendance_date')->limit(60)->get());}
 public function clockIn(Request $r){$e=$this->employee($r);$rec=AttendanceRecord::firstOrCreate(['employee_id'=>$e->id,'attendance_date'=>today()],['property_id'=>$e->property_id,'status'=>'present','source'=>'mobile']);if(!$rec->clock_in)$rec->update(['clock_in'=>now()]);return response()->json($rec->refresh());}
 public function clockOut(Request $r){$e=$this->employee($r);$rec=AttendanceRecord::where('employee_id',$e->id)->whereDate('attendance_date',today())->firstOrFail();$out=now();$hours=$rec->clock_in?max(0,round($rec->clock_in->diffInMinutes($out)/60,2)):0;$rec->update(['clock_out'=>$out,'hours_worked'=>$hours]);return response()->json($rec->refresh());}
 public function leaveTypes(Request $r){$e=$this->employee($r);return response()->json(LeaveType::where('property_id',$e->property_id)->where('is_active',1)->get());}
 public function leave(Request $r){$e=$this->employee($r);$d=$r->validate(['leave_type_id'=>'required|integer','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','reason'=>'nullable|string|max:1000']);$d['property_id']=$e->property_id;$d['employee_id']=$e->id;$d['days']=(new \DateTime($d['start_date']))->diff(new \DateTime($d['end_date']))->days+1;return response()->json(LeaveRequest::create($d),201);}
}
