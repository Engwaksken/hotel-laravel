<?php
namespace App\Services\Operations;
use App\Models\LaundryOrder;use App\Services\Folios\FolioService;use Illuminate\Support\Facades\DB;
class LaundryServiceManager{
 public function __construct(private FolioService $folios){}
 public function setStatus(LaundryOrder $order,string $status,int $userId):LaundryOrder{return DB::transaction(function()use($order,$status,$userId){$order->load('folio');$data=['status'=>$status];if($status==='delivered'){$data['delivered_at']=now();if($order->type==='guest' && $order->folio && !$order->posted_to_folio && (float)$order->total>0){$this->folios->addCharge($order->folio,['department'=>'laundry','description'=>'Laundry '.$order->reference,'quantity'=>1,'unit_price'=>(float)$order->total,'tax_amount'=>0],$userId);$data['posted_to_folio']=true;}}$order->update($data);return $order->fresh();});}
}
