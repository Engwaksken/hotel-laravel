<?php
namespace App\Services\Operations;
use App\Models\{Folio,GuestServiceRequest};use App\Services\Folios\FolioService;use Illuminate\Support\Facades\DB;
class GuestServiceManager{
 public function __construct(private FolioService $folios){}
 public function updateStatus(GuestServiceRequest $request,string $status,int $userId,?int $assignee=null,?string $notes=null):GuestServiceRequest{return DB::transaction(function()use($request,$status,$userId,$assignee,$notes){$data=['status'=>$status];if($assignee)$data['assigned_to']=$assignee;if($status==='completed'){$data['completed_at']=now();$data['completion_notes']=$notes;if(!$request->charge_posted && (float)$request->charge_amount>0 && $request->stay_id){$folio=Folio::where('stay_id',$request->stay_id)->where('status','open')->first();if($folio){$this->folios->addCharge($folio,['department'=>'guest_services','description'=>'Guest service: '.ucwords(str_replace('_',' ',$request->request_type)),'quantity'=>1,'unit_price'=>(float)$request->charge_amount,'tax_amount'=>0],$userId);$data['charge_posted']=true;}}}$request->update($data);return $request->fresh();});}
}
