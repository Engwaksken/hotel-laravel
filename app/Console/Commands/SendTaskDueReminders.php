<?php
namespace App\Console\Commands;
use App\Services\Tasks\TaskNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
class SendTaskDueReminders extends Command {
    protected $signature='tasks:send-due-reminders'; protected $description='Send reminders for assigned hotel tasks due within 30 minutes.';
    public function handle(): int {
        $now=now(); $until=$now->copy()->addMinutes(30); $count=0;
        foreach(TaskNotificationService::supported() as $class=>$cfg){
            $model=new $class; $table=$model->getTable();
            if(!Schema::hasColumn($table,'assigned_to') || !Schema::hasColumn($table,'due_at')) continue;
            $q=$class::query()->whereNotNull('assigned_to')->whereNotNull('due_at')->where('due_at','>',$now)->where('due_at','<=',$until);
            if(Schema::hasColumn($table,'status')) $q->whereNotIn('status',$cfg['done']);
            $q->orderBy('due_at')->chunkById(100,function($tasks) use(&$count){ foreach($tasks as $task){ TaskNotificationService::dueReminder($task); $count++; }});
        }
        $this->info("Checked {$count} due task(s)."); return self::SUCCESS;
    }
}
