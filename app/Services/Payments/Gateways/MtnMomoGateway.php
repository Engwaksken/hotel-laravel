<?php
namespace App\Services\Payments\Gateways;
use Illuminate\Support\Str;
class MtnMomoGateway extends AbstractHttpGateway{
 private function cfg(string $k){return $this->setting($k, config("payment_gateways.mtn_momo.$k"));}
 private function token():string{$u=$this->required($this->cfg('api_user'),'MTN_MOMO_API_USER');$k=$this->required($this->cfg('api_key'),'MTN_MOMO_API_KEY');$r=$this->http(['Ocp-Apim-Subscription-Key'=>$this->required($this->cfg('subscription_key'),'MTN_MOMO_SUBSCRIPTION_KEY')])->withBasicAuth($u,$k)->post(rtrim($this->cfg('base_url'),'/').'/collection/token/')->throw()->json();return $this->required($r['access_token']??null,'MTN access token');}
 private function headers():array{return ['Authorization'=>'Bearer '.$this->token(),'X-Target-Environment'=>$this->cfg('target_environment'),'Ocp-Apim-Subscription-Key'=>$this->required($this->cfg('subscription_key'),'MTN_MOMO_SUBSCRIPTION_KEY')];}
 public function initiate(array $p):array{$ref=(string)Str::uuid();$payload=['amount'=>(string)$p['amount'],'currency'=>$p['currency']??'UGX','externalId'=>$p['reference'],'payer'=>['partyIdType'=>'MSISDN','partyId'=>preg_replace('/\D/','',$p['phone']??'')],'payerMessage'=>$p['description']??'Hotel payment','payeeNote'=>'Hotel payment'];$this->http($this->headers()+['X-Reference-Id'=>$ref])->post(rtrim($this->cfg('base_url'),'/').'/collection/v1_0/requesttopay',$payload)->throw();return ['mode'=>'push','gateway'=>'mtn_momo','gateway_reference'=>$ref,'reference'=>$p['reference']];}
 public function verify(string $reference):array{$r=$this->http($this->headers())->get(rtrim($this->cfg('base_url'),'/').'/collection/v1_0/requesttopay/'.$reference)->throw()->json();return ['successful'=>($r['status']??null)==='SUCCESSFUL','status'=>strtolower($r['status']??'unknown'),'amount'=>(float)($r['amount']??0),'currency'=>$r['currency']??null,'gateway_reference'=>$reference,'raw'=>$r];}
}
