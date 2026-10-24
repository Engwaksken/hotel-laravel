<?php
namespace App\Services\Payments\Contracts;
interface PaymentGateway { public function initiate(array $payload): array; public function verify(string $reference): array; public function refund(string $reference, float $amount): array; }
