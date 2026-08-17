<?php

namespace Tests\Unit\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\PayPalGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_activates_plan_before_creating_subscription(): void
    {
        config(['app.name' => 'SocialSyncBot']);

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'token_123'], 200);
            }

            if (str_contains($url, '/v1/catalogs/products')) {
                return Http::response([
                    'id' => 'PROD-1234567890',
                ], 201);
            }

            if (str_contains($url, '/v1/billing/plans') && ! str_contains($url, '/activate')) {
                return Http::response([
                    'id' => 'P-1234567890',
                    'status' => 'CREATED',
                ], 201);
            }

            if (str_contains($url, '/v1/billing/plans/P-1234567890/activate')) {
                return Http::response(null, 204);
            }

            if (str_contains($url, '/v1/billing/subscriptions')) {
                return Http::response([
                    'id' => 'I-1234567890',
                    'links' => [
                        ['rel' => 'approve', 'href' => 'https://paypal.test/approve'],
                    ],
                ], 201);
            }

            return Http::response([], 404);
        });

        $gateway = new PayPalGateway(
            'client-id',
            'client-secret',
            true,
            'https://example.com/success',
            'https://example.com/cancel',
            ''
        );

        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $plan = Plan::factory()->create([
            'name' => 'Starter',
            'monthly_price_cents' => 1500,
            'yearly_price_cents' => 15000,
            'currency_code' => 'USD',
        ]);

        $result = $gateway->createCheckout($user, $plan, 'month');

        $this->assertSame('https://paypal.test/approve', $result['url']);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/billing/plans/P-1234567890/activate'));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/billing/subscriptions'));
    }
}
