<?php
namespace App\Http\Controllers\Management;
use App\Http\Controllers\Controller; use App\Support\HmsPermissionResolver; use Illuminate\Http\Request; use Illuminate\Support\Facades\{DB,Schema};
class SystemHealthController extends Controller {
 public function __invoke(Request $r,HmsPermissionResolver $p){abort_unless($p->allows($r->user(),'security.audit'),403);$latest=Schema::hasTable('system_health_snapshots')?DB::table('system_health_snapshots')->latest('checked_at')->first():null;$failed=Schema::hasTable('failed_jobs')?DB::table('failed_jobs')->latest('failed_at')->limit(20)->get():collect();return view('management.health',compact('latest','failed'));}
}
