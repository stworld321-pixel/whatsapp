<?php

namespace Tests\Feature\ProductionHardening;

use App\Models\Client;
use App\Models\ClientSubscription;
use App\Models\Plan;
use App\Modules\AI\Models\AiChatbot;
use App\Modules\Automation\Models\Automation;
use App\Modules\Broadcasting\Models\Campaign;
use App\Modules\Whatsapp\Models\WhatsappBusinessAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Plan-limit enforcement tests.
 */
class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_launch_returns_redirect_when_limit_exhausted(): void
    {
        Queue::fake();

        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $workspace = $data['workspace'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['campaigns_per_month' => 0]]);
        $this->attachPlanToClient($client, $plan);

        $campaign = Campaign::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => 'draft',
            'channel' => 'sms',
        ]);

        $response = $this->actingAs($user)
            ->post(route('client.campaigns.launch', $campaign));

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
    }

    public function test_lead_scrape_returns_redirect_when_limit_exhausted(): void
    {
        Queue::fake();

        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $workspace = $data['workspace'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['lead_credits_per_month' => 0]]);
        $this->attachPlanToClient($client, $plan);

        $response = $this->actingAs($user)->post('/app/leads/scrape', [
            'query' => 'restaurants',
            'location' => 'Dhaka',
        ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
    }

    public function test_social_connect_requires_an_active_plan(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];

        $response = $this->actingAs($user)
            ->get(route('client.social.accounts.connect', ['network' => 'facebook']));

        $response->assertRedirect(route('client.social.accounts.index', absolute: false));
        $response->assertSessionHas('error', 'A plan is required before connecting social accounts.');
    }

    public function test_whatsapp_embedded_signup_requires_an_active_plan(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];

        $response = $this->actingAs($user)->postJson(route('client.whatsapp.setup.embedded-signup'), [
            'code' => 'test-code',
            'waba_id' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'A plan is required before connecting WhatsApp accounts.',
        ]);
    }

    public function test_channel_setup_page_renders_without_a_plan(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];

        $response = $this->actingAs($user)->get(route('client.inbox.setup'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Inbox/Setup')
            ->where('planRequired', true)
            ->where('planRequiredMessage', 'A plan is required before connecting channels. Please activate a plan to continue.')
        );
    }

    public function test_admin_assigned_monthly_plan_gets_an_expiry_date(): void
    {
        Carbon::setTestNow('2026-08-18 10:00:00');

        $admin = $this->createSuperAdmin();
        $client = Client::create([
            'name' => 'Expiry Client',
            'email' => 'expiry@example.com',
            'status' => Client::STATUS_ACTIVE,
        ]);
        $plan = Plan::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.clients.assign-plan', $client), [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect();

        $subscription = ClientSubscription::where('client_id', $client->id)->latest('id')->first();
        $this->assertNotNull($subscription);
        $this->assertSame('monthly', $subscription->billing_cycle);
        $this->assertSame('2026-09-18 10:00:00', $subscription->ends_at?->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_expired_client_subscription_is_not_considered_active(): void
    {
        Carbon::setTestNow('2026-08-18 10:00:00');

        $client = Client::create([
            'name' => 'Expired Client',
            'email' => 'expired@example.com',
            'status' => Client::STATUS_ACTIVE,
        ]);
        $plan = Plan::factory()->create();

        ClientSubscription::create([
            'client_id' => $client->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'status' => ClientSubscription::STATUS_ACTIVE,
        ]);

        $this->assertNull($client->fresh()->activePlan());

        Carbon::setTestNow();
    }

    public function test_chatbot_creation_is_blocked_when_plan_limit_is_exhausted(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $workspace = $data['workspace'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['chatbots' => 0]]);
        $this->attachPlanToClient($client, $plan);

        AiChatbot::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('client.ai.chatbots.store'), [
            'name' => 'Blocked Bot',
        ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
        $this->assertDatabaseMissing('ai_chatbots', ['name' => 'Blocked Bot']);
    }

    public function test_automation_creation_is_blocked_when_plan_limit_is_exhausted(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $workspace = $data['workspace'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['automations' => 0]]);
        $this->attachPlanToClient($client, $plan);

        Automation::create([
            'workspace_id' => $workspace->id,
            'name' => 'Existing automation',
            'status' => 'draft',
            'nodes' => [],
            'edges' => [],
        ]);

        $response = $this->actingAs($user)->post(route('client.automations.store'), [
            'name' => 'Blocked Automation',
        ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
        $this->assertDatabaseMissing('automations', ['name' => 'Blocked Automation']);
    }

    public function test_whatsapp_template_creation_is_blocked_when_plan_limit_is_exhausted(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $workspace = $data['workspace'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['whatsapp_templates' => 0]]);
        $this->attachPlanToClient($client, $plan);

        WhatsappBusinessAccount::create([
            'workspace_id' => $workspace->id,
            'waba_id' => '123456789012345',
            'status' => 'active',
            'credentials' => ['access_token' => 'test-token'],
            'meta_json' => [],
        ]);

        $response = $this->actingAs($user)->post(route('client.whatsapp.templates.store'), [
            'name' => 'blocked_template',
            'language' => 'en',
            'category' => 'MARKETING',
            'components' => [
                ['type' => 'BODY', 'text' => 'Hello'],
            ],
        ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
        $this->assertDatabaseMissing('whatsapp_templates', ['name' => 'blocked_template']);
    }

    public function test_team_member_creation_is_blocked_when_plan_limit_is_exhausted(): void
    {
        $data = $this->createWorkspaceContext();
        $user = $data['user'];
        $client = $data['client'];

        $plan = Plan::factory()->create(['limits' => ['users' => 1]]);
        $this->attachPlanToClient($client, $plan);

        $response = $this->actingAs($user)->post(route('client.team.store'), [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'client_role' => 'staff',
            'status' => 'active',
        ]);

        $response->assertRedirect('/billing');
        $response->assertSessionHas('upgrade_required');
        $this->assertDatabaseMissing('users', ['email' => 'second@example.com']);
    }
}
