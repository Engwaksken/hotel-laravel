<?php
namespace App\Console\Commands;use App\Models\Property;use App\Services\Management\AnalyticsService;use Illuminate\Console\Command;
class CreateManagementSnapshots extends Command{protected $signature='hotel:management-snapshot {--date=}';protected $description='Create daily management KPI snapshots';public function handle(AnalyticsService $a):int{$d=\Carbon\Carbon::parse($this->option('date')?:now()->subDay()->toDateString());Property::where('is_active',1)->each(fn($p)=>$a->snapshot($p->id,$d));$this->info('Management snapshots created for '.$d->toDateString());return self::SUCCESS;}}
