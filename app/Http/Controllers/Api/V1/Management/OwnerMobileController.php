<?php

namespace App\Http\Controllers\Api\V1\Management;

use App\Http\Controllers\Controller;
use App\Models\{ManagementDailySnapshot,Property,SecurityEvent};
use App\Services\Management\AnalyticsService;
use App\Support\HmsPermissionResolver;
use Illuminate\Http\Request;

class OwnerMobileController extends Controller
{
    public function dashboard(Request $request, AnalyticsService $analytics, HmsPermissionResolver $permissions)
    {
        abort_unless($permissions->allows($request->user(), 'management.view'), 403);
        $user = $request->user();
        $propertyIds = $user->role === 'super_admin' ? Property::where('is_active', true)->pluck('id') : collect([$user->property_id])->filter();
        $from = now()->subDays(29)->toDateString(); $to = now()->toDateString();
        $properties = Property::whereIn('id', $propertyIds)->get()->map(function ($property) use ($analytics, $from, $to) {
            $metrics = $analytics->propertyKpis($property->id, \Carbon\Carbon::parse($from), \Carbon\Carbon::parse($to));
            return ['id'=>$property->id,'name'=>$property->name,'metrics'=>$metrics];
        });
        return response()->json(['period'=>['from'=>$from,'to'=>$to],'properties'=>$properties]);
    }

    public function snapshots(Request $request, HmsPermissionResolver $permissions)
    {
        abort_unless($permissions->allows($request->user(), 'management.view'), 403);
        $q = ManagementDailySnapshot::query()->orderByDesc('business_date')->limit(60);
        if ($request->user()->role !== 'super_admin') $q->where('property_id', $request->user()->property_id);
        return response()->json(['data'=>$q->get()]);
    }

    public function alerts(Request $request, HmsPermissionResolver $permissions)
    {
        abort_unless($permissions->allows($request->user(), 'security.audit'), 403);
        $q = SecurityEvent::query()->whereIn('severity',['warning','critical'])->latest('occurred_at')->limit(30);
        if ($request->user()->role !== 'super_admin') $q->where('property_id',$request->user()->property_id);
        return response()->json(['data'=>$q->get()]);
    }
}
