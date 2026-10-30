<?php

namespace Tests\Feature;

use App\Models\PaymentGatewayConfig;
use App\Models\Property;
use App\Services\Payments\PaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PropertyPaymentGatewayConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_credentials_are_used_for_flutterwave_checkout(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://payments.example.test/*' => Http::response(['status' => 'success', 'data' => ['id' => 10]], 200)]);

        $property = Property::factory()->create();
        PaymentGatewayConfig::create([
            'property_id' => $property->id,
            'gateway' => 'flutterwave',
            'label' => 'Property Flutterwave',
            'enabled' => true,
            'sandbox' => false,
            'credentials' => ['secret_key' => 'property-secret'],
            'settings' => ['base_url' => 'https://payments.example.test/v3', 'currency' => 'UGX'],
        ]);

        app(PaymentManager::class)->gateway('flutterwave', $property->id)->initiate([
            'reference' => 'PAY-PROPERTY-1',
            'amount' => 100,
            'currency' => 'UGX',
            'email' => 'guest@example.test',
            'phone' => '256700000000',
            'name' => 'Test Guest',
        ]);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://payments.example.test/v3/charges?type=mobile_money_uganda'
            && $request->hasHeader('Authorization', 'Bearer property-secret')
            && $request['tx_ref'] === 'PAY-PROPERTY-1'
        );
    }

    public function test_disabled_property_gateway_cannot_be_used(): void
    {
        $property = Property::factory()->create();
        PaymentGatewayConfig::create([
            'property_id' => $property->id,
            'gateway' => 'flutterwave',
            'label' => 'Disabled Flutterwave',
            'enabled' => false,
            'sandbox' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(PaymentManager::class)->gateway('flutterwave', $property->id);
    }
}
