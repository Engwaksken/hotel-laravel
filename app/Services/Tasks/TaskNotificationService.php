<?php
namespace App\Services\Tasks;
use App\Models\{GuestComplaint,GuestServiceRequest,HousekeepingTask,LaundryOrder,MaintenanceRequest,RoomServiceRequest,TaskNotificationLog,User};
use App\Notifications\{TaskAssignedNotification,TaskDueSoonNotification};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
class TaskNotificationService {
    public static function supported(): array { return [
        HousekeepingTask::class => ['url'=>'operations.housekeeping.index','done'=>['inspected','cancelled']],
        MaintenanceRequest::class => ['url'=>'operations.maintenance.index','done'=>['resolved','closed','cancelled']],
        GuestServiceRequest::class => ['url'=>'operations.guest-services.index','done'=>['completed','cancelled']],
        RoomServiceRequest::class => ['url'=>'pos.index','done'=>['delivered','cancelled']],
        LaundryOrder::class => ['url'=>'operations.laundry.index','done'=>['delivered','cancelled']],
        GuestComplaint::class => ['url'=>'crm.complaints.index','done'=>['resolved','closed','cancelled']],
    ]; }
    public static function payload(Model $task): array {
        $route=self::supported()[$task::class]['url'] ?? 'dashboard';
        $title=$task->title ?? $task->reference ?? $task->request_type ?? $task->type ?? class_basename($task);
        $room=$task->room?->room_number ?? null;
        return ['title'=>(string)$title,'message'=>trim(($room?'Room '.$room.' · ':'').ucwords(str_replace('_',' ',(string)($task->status ?? 'assigned')))),'task_type'=>class_basename($task),'task_id'=>$task->getKey(),'due_at'=>$task->due_at?->format('d M Y, h:i A'),'priority'=>$task->priority ?? null,'url'=>route($route)];
    }
    public static function assignment(Model $task): void {
        if (!$task->assigned_to) return;
        $user=User::whereKey($task->assigned_to)->where('is_active',1)->first(); if(!$user) return;
        $key='assigned:'.$user->id.':'.($task->updated_at?->timestamp ?? now()->timestamp);
        self::sendOnce($task,$user,$key,new TaskAssignedNotification(self::payload($task)));
    }
    public static function dueReminder(Model $task): void {
        if (!$task->assigned_to || !$task->due_at) return;
        $user=User::whereKey($task->assigned_to)->where('is_active',1)->first(); if(!$user) return;
        $key='due30:'.$task->due_at->format('YmdHi');
        self::sendOnce($task,$user,$key,new TaskDueSoonNotification(self::payload($task)));
    }
    private static function sendOnce(Model $task,User $user,string $key,$notification): void {
        try {
            DB::transaction(function() use($task,$user,$key,$notification){
                $log=TaskNotificationLog::firstOrCreate(['task_type'=>$task::class,'task_id'=>$task->getKey(),'user_id'=>$user->id,'event_key'=>$key],['sent_at'=>now()]);
                if(!$log->wasRecentlyCreated) return;
                $user->notify($notification);
            });
        } catch (\Throwable $e) { report($e); }
    }
    public static function dashboardTasks(User $user): Collection {
        $all=collect();
        foreach(self::supported() as $class=>$cfg){
            $q=$class::query()->where('assigned_to',$user->id);
            if($user->property_id && \Illuminate\Support\Facades\Schema::hasColumn((new $class)->getTable(),'property_id')) $q->where('property_id',$user->property_id);
            if(\Illuminate\Support\Facades\Schema::hasColumn((new $class)->getTable(),'status')) $q->whereNotIn('status',$cfg['done']);
            $q->latest()->limit(8)->get()->each(function($task) use(&$all){ $all->push((object)(self::payload($task)+['model'=>$task])); });
        }
        return $all->sortBy(fn($x)=>$x->model->due_at?->timestamp ?? PHP_INT_MAX)->take(10)->values();
    }
}
