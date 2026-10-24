<?php
namespace App\Http\Controllers;
use App\Enums\PaymentStatus;
use App\Models\{Payment,Reservation,Room,Stay};
use App\Services\Tasks\TaskNotificationService;
use Illuminate\Http\Request;
class DashboardController extends Controller {
    public function __invoke(Request $r){
        $pid=$r->user()->property_id;$today=now()->startOfDay();$tomorrow=now()->copy()->addDay()->startOfDay();$rooms=Room::where('property_id',$pid);
        $stats=['rooms'=>(clone $rooms)->count(),'available'=>(clone $rooms)->where('status','available')->count(),'occupied'=>(clone $rooms)->where('status','occupied')->count(),'dirty'=>(clone $rooms)->where('housekeeping_status','dirty')->count(),'arrivals'=>Reservation::where('property_id',$pid)->whereBetween('check_in_at',[$today,$tomorrow])->whereNotIn('status',['cancelled','checked_out'])->count(),'departures'=>Stay::where('property_id',$pid)->whereBetween('expected_check_out_at',[$today,$tomorrow])->where('status','in_house')->count(),'in_house'=>Stay::where('property_id',$pid)->where('status','in_house')->count(),'revenue'=>Payment::where('property_id',$pid)->where('status',PaymentStatus::Successful)->whereBetween('paid_at',[$today,$tomorrow])->sum('amount')];
        $stats['occupancy']=$stats['rooms']?round(($stats['occupied']/$stats['rooms'])*100,1):0;
        $arrivals=Reservation::with(['guest','rooms.room'])->where('property_id',$pid)->whereBetween('check_in_at',[$today,$tomorrow])->whereNotIn('status',['cancelled','checked_out'])->orderBy('check_in_at')->limit(8)->get();
        $departures=Stay::with(['guest','room'])->where('property_id',$pid)->whereBetween('expected_check_out_at',[$today,$tomorrow])->where('status','in_house')->orderBy('expected_check_out_at')->limit(8)->get();
        $myTasks=TaskNotificationService::dashboardTasks($r->user());
        return view('dashboard',compact('stats','arrivals','departures','myTasks'));
    }
}
