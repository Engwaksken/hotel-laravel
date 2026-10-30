<?php
namespace App\Services\Payments\Gateways;
class FlutterwaveGateway extends AbstractHttpGateway{
 private function cfg(string $k){return $this->setting($k, config("payment_gateways.flutterwave.$k"));}
 public function initiate(array $p):array{$key=$this->required($this->cfg('secret_key'),'FLUTTERWAVE_SECRET_KEY');$payload=['tx_ref'=>$p['reference'],'amount'=>$p['amount'],'currency'=>$p['currency']??'UGX','email'=>$p['email'],'phone_number'=>$p['phone'],'fullname'=>$p['name']??'Hotel Guest'];$r=$this->http(['Authorization'=>'Bearer '.$key])->post(rtrim($this->cfg('base_url'),'/').'/charges?type=mobile_money_uganda',$payload)->throw()->json();return ['mode'=>'provider','gateway'=>'flutterwave','response'=>$r,'reference'=>$p['reference']];}
 public function verify(string $reference):array{$key=$this->required($this->cfg('secret_key'),'FLUTTERWAVE_SECRET_KEY');$r=$this->http(['Authorization'=>'Bearer '.$key])->get(rtrim($this->cfg('base_url'),'/').'/transactions',['tx_ref'=>$reference])->throw()->json();$data=$r['data'][0]??$r['data']??[];return ['successful'=>($data['status']??null)==='successful','status'=>$data['status']??'unknown','amount'=>(float)($data['amount']??0),'currency'=>$data['currency']??null,'gateway_reference'=>(string)($data['id']??$data['flw_ref']??''),'raw'=>$r];}
}
