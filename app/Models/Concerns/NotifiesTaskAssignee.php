<?php
namespace App\Models\Concerns;
use App\Services\Tasks\TaskNotificationService;
use Illuminate\Support\Facades\DB;
trait NotifiesTaskAssignee {
    protected static function bootNotifiesTaskAssignee(): void {
        static::saved(function($model){
            if(!$model->assigned_to) return;
            if($model->wasRecentlyCreated || $model->wasChanged('assigned_to')) {
                DB::afterCommit(fn()=>TaskNotificationService::assignment($model->fresh()));
            }
        });
    }
}
