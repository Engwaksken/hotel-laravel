<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Models\PaymentTransaction;
use App\Models\Property;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payment_gateways.flutterwave.webhook_secret' => 'test-webhook-secret']);
    }

    public function test_flutterwave_webhook_rejects_missing_or_invalid_signature(): void
    {
        $payment = $this->pendingFlutterwavePayment();
        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldNotReceive('verify');
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldNotReceive('gateway');
        $this->instance(PaymentManager::class, $manager);

        $this->postJson('/api/v1/payments/webhook/flutterwave', ['tx_ref' => $payment->reference])
            ->assertUnauthorized();

        $this->postJson('/api/v1/payments/webhook/flutterwave', ['tx_ref' => $payment->reference], [
            'verif-hash' => 'wrong-secret',
        ])->assertUnauthorized();

        $this->assertSame(PaymentStatus::Processing, $payment->fresh()->status);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    public function test_replayed_successful_webhook_does_not_duplicate_or_regress_payment(): void
    {
        $payment = $this->pendingFlutterwavePayment();
        $verification = [
            'successful' => true,
            'status' => 'successful',
            'amount' => 125.00,
            'currency' => 'UGX',
            'gateway_reference' => 'flw-tx-123',
            'raw' => ['status' => 'successful'],
        ];
        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('verify')->twice()->with($payment->reference)->andReturn($verification);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('gateway')->twice()->with('flutterwave', $payment->property_id)->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $payload = ['tx_ref' => $payment->reference, 'id' => 'flw-tx-123'];
        $headers = ['verif-hash' => 'test-webhook-secret'];

        $this->postJson('/api/v1/payments/webhook/flutterwave', $payload, $headers)->assertOk();
        $paidAt = $payment->fresh()->paid_at;
        $this->postJson('/api/v1/payments/webhook/flutterwave', $payload, $headers)->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatus::Successful, $payment->status);
        $this->assertSame('flw-tx-123', $payment->gateway_reference);
        $this->assertSame($paidAt->toDateTimeString(), $payment->paid_at->toDateTimeString());
        $this->assertDatabaseCount('payment_transactions', 1);
    }

    public function test_webhook_does_not_mark_payment_successful_when_amount_does_not_match(): void
    {
        $payment = $this->pendingFlutterwavePayment();
        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('verify')->once()->andReturn([
            'successful' => true,
            'status' => 'successful',
            'amount' => 100.00,
            'currency' => 'UGX',
            'gateway_reference' => 'flw-tx-short',
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('gateway')->once()->with('flutterwave', $payment->property_id)->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->postJson('/api/v1/payments/webhook/flutterwave', [
            'tx_ref' => $payment->reference,
            'id' => 'flw-tx-short',
        ], ['verif-hash' => 'test-webhook-secret'])->assertOk();

        $this->assertSame(PaymentStatus::Processing, $payment->fresh()->status);
    }

    public function test_flutterwave_signature_can_use_the_encrypted_property_gateway_secret(): void
    {
        config(['payment_gateways.flutterwave.webhook_secret' => null]);
        $payment = $this->pendingFlutterwavePayment();
        PaymentGatewayConfig::create([
            'property_id' => $payment->property_id,
            'gateway' => 'flutterwave',
            'label' => 'Flutterwave test',
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['webhook_secret' => 'property-webhook-secret'],
            'settings' => ['currency' => 'UGX'],
        ]);

        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('verify')->once()->with($payment->reference)->andReturn([
            'successful' => false,
            'status' => 'pending',
            'amount' => 125.00,
            'currency' => 'UGX',
            'gateway_reference' => 'flw-pending',
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('gateway')->once()->with('flutterwave', $payment->property_id)->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->postJson('/api/v1/payments/webhook/flutterwave', [
            'tx_ref' => $payment->reference,
            'id' => 'flw-pending',
        ], ['verif-hash' => 'property-webhook-secret'])->assertOk();
    }

    private function pendingFlutterwavePayment(): Payment
    {
        $property = Property::factory()->create();

        return Payment::create([
            'property_id' => $property->id,
            'reference' => 'PAY-'.strtoupper(fake()->unique()->bothify('??????????')),
            'method' => 'flutterwave',
            'gateway' => 'flutterwave',
            'status' => PaymentStatus::Processing,
            'currency' => 'UGX',
            'amount' => 125,
        ]);
    }
}
