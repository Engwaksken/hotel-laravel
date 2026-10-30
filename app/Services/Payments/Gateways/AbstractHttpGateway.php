<?php
namespace App\Services\Payments\Gateways;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Client\PendingRequest; use Illuminate\Support\Facades\Http; use RuntimeException;
abstract class AbstractHttpGateway implements PaymentGateway {
 public function __construct(protected array $configuration = []) {}
 protected function setting(string $key, mixed $fallback = null): mixed { return data_get($this->configuration, $key, $fallback); }
 protected function http(array $headers=[]):PendingRequest{return Http::acceptJson()->asJson()->timeout(30)->retry(2,300)->withHeaders($headers);}
 protected function required(mixed $value,string $name):mixed{if(blank($value))throw new RuntimeException("Missing payment configuration: {$name}");return $value;}
 public function refund(string $reference,float $amount):array{return ['mode'=>'manual_refund','reference'=>$reference,'amount'=>$amount,'message'=>'Refund requires provider-specific enablement and approval.'];}
}
