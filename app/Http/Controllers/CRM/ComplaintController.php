<?php
namespace App\Http\Controllers\CRM;
use App\Http\Controllers\Controller;
use App\Models\{Guest,GuestComplaint,User};
use Illuminate\Http\Request;
class ComplaintController extends Controller {
    public function index(){ $p=auth()->user()->property_id; return view('crm.complaints',['complaints'=>GuestComplaint::where('property_id',$p)->with(['guest','assignee'])->latest()->paginate(30),'guests'=>Guest::where('property_id',$p)->orderBy('first_name')->limit(250)->get(),'staff'=>User::where('property_id',$p)->where('is_active',1)->orderBy('name')->get()]);}
    public function store(Request $r){$d=$r->validate(['guest_id'=>'nullable|exists:guests,id','category'=>'required|string|max:60','priority'=>'required|in:low,medium,high,urgent','description'=>'required|string','assigned_to'=>'nullable|integer','due_at'=>'nullable|date']);$d+=['property_id'=>auth()->user()->property_id,'reference'=>'CMP-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6))];GuestComplaint::create($d);return back()->with('success','Complaint recorded.');}
    public function resolve(Request $r,GuestComplaint $complaint){abort_unless($complaint->property_id===auth()->user()->property_id,403);$d=$r->validate(['resolution'=>'required|string']);$complaint->update(['status'=>'resolved','resolution'=>$d['resolution'],'resolved_at'=>now(),'resolved_by'=>auth()->id()]);return back()->with('success','Complaint resolved.');}
}
