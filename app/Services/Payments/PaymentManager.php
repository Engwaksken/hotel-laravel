<?php

namespace App\Services\Payments;

use App\Models\PaymentGatewayConfig;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Gateways\AirtelMoneyGateway;
use App\Services\Payments\Gateways\FlutterwaveGateway;
use App\Services\Payments\Gateways\MtnMomoGateway;
use App\Services\Payments\Gateways\PesapalGateway;
use InvalidArgumentException;

class PaymentManager
{
    public function __construct(private array $gateways = []) {}

    public function register(string $name, PaymentGateway $gateway): self
    {
        $this->gateways[$name] = $gateway;
        return $this;
    }

    public function gateway(string $name, ?int $propertyId = null): PaymentGateway
    {
        $canonical = $name === 'card' ? 'flutterwave' : ($name === 'airtel_money' ? 'airtel_momo' : $name);
        $class = match ($canonical) {
            'mtn_momo' => MtnMomoGateway::class,
            'airtel_momo' => AirtelMoneyGateway::class,
            'pesapal' => PesapalGateway::class,
            'flutterwave' => FlutterwaveGateway::class,
            default => throw new InvalidArgumentException("Payment gateway [{$name}] is not registered."),
        };

        if ($propertyId !== null) {
            $profile = PaymentGatewayConfig::query()
                ->where('property_id', $propertyId)
                ->whereIn('gateway', $canonical === 'flutterwave' ? ['flutterwave', 'card'] : [$canonical, $name])
                ->first();

            if ($profile) {
                if (! $profile->enabled) {
                    throw new InvalidArgumentException("Payment gateway [{$name}] is disabled for this property.");
                }

                $credentials = $profile->credentials ?? [];
                $settings = $profile->settings ?? [];
                $configuration = match ($canonical) {
                    'mtn_momo' => [
                        'api_user' => $credentials['api_user'] ?? null,
                        'api_key' => $credentials['api_key'] ?? null,
                        'subscription_key' => $credentials['subscription_key'] ?? null,
                        'target_environment' => $settings['target_environment'] ?? ($profile->sandbox ? 'sandbox' : 'production'),
                        'base_url' => $settings['base_url'] ?? null,
                    ],
                    'airtel_momo' => [
                        'client_id' => $credentials['client_id'] ?? null,
                        'client_secret' => $credentials['client_secret'] ?? null,
                        'country' => $settings['country'] ?? config('payment_gateways.airtel_momo.country'),
                        'currency' => $settings['currency'] ?? 'UGX',
                        'base_url' => $settings['base_url'] ?? null,
                    ],
                    'pesapal' => [
                        'consumer_key' => $credentials['consumer_key'] ?? null,
                        'consumer_secret' => $credentials['consumer_secret'] ?? null,
                        'ipn_id' => $credentials['ipn_id'] ?? null,
                        'base_url' => $settings['base_url'] ?? null,
                    ],
                    default => [
                        'secret_key' => $credentials['secret_key'] ?? $credentials['api_key'] ?? null,
                        'webhook_secret' => $credentials['webhook_secret'] ?? null,
                        'base_url' => $settings['base_url'] ?? null,
                    ],
                };

                foreach ($configuration as $key => $value) {
                    if ($value === null || $value === '') {
                        unset($configuration[$key]);
                    }
                }

                return app()->make($class, ['configuration' => $configuration]);
            }
        }

        return $this->gateways[$name]
            ?? $this->gateways[$canonical]
            ?? app($class);
    }
}
