<?php

namespace Tests\Feature\Polish;

use App\Models\Plan;
use App\Services\MediaService;
use App\Modules\Broadcasting\Models\UsageMeter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlanLimitAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_usage_meter_tracks_whatsapp_messages(): void
    {
        $ctx = $this->createWorkspaceContext([], ['email_verified_at' => now()]);
        $workspace = $ctx['workspace'];

        UsageMeter::track($workspace->id, 'whatsapp_messages', 3);

        $total = DB::table('usage_meters')
            ->where('workspace_id', $workspace->id)
            ->where('metric', 'whatsapp_messages')
            ->sum('value');

        $this->assertEquals(3, $total);
    }

    public function test_usage_meter_tracks_social_posts(): void
    {
        $ctx = $this->createWorkspaceContext([], ['email_verified_at' => now()]);
        $workspace = $ctx['workspace'];

        UsageMeter::track($workspace->id, 'social_posts', 1);

        $total = DB::table('usage_meters')
            ->where('workspace_id', $workspace->id)
            ->where('metric', 'social_posts')
            ->sum('value');

        $this->assertEquals(1, $total);
    }

    public function test_media_quota_uses_storage_mb_plan_limit(): void
    {
        $ctx = $this->createWorkspaceContext();
        $user = $ctx['user'];
        $client = $ctx['client'];

        $plan = Plan::factory()->create(['limits' => ['storage' => 2048]]);
        $this->attachPlanToClient($client, $plan);

        $quotaBytes = app(MediaService::class)->quotaBytes($user);

        $this->assertSame(2048 * 1024 * 1024, $quotaBytes);
    }

    public function test_dashboard_shared_usage_includes_storage_and_inbox_agents(): void
    {
        $ctx = $this->createWorkspaceContext();
        $user = $ctx['user'];
        $workspace = $ctx['workspace'];
        $client = $ctx['client'];

        $plan = Plan::factory()->create(['limits' => [
            'storage' => 1024,
            'inbox_agents' => 5,
        ]]);
        $this->attachPlanToClient($client, $plan);

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('current_workspace_usage.storage.limit', 1024 * 1024 * 1024)
            ->where('current_workspace_usage.storage.current', 0)
            ->where('current_workspace_usage.inbox_agents.current', 1)
            ->where('current_workspace_usage.inbox_agents.limit', 5)
        );
    }
}
