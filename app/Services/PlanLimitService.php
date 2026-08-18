<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Workspace;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Social\Models\SocialAccount;
use App\Modules\Whatsapp\Models\WhatsappBusinessAccount;

class PlanLimitService
{
    public function planForWorkspace(int $workspaceId): ?Plan
    {
        $workspace = Workspace::with('client')->find($workspaceId);

        return $workspace?->client?->effectivePlan();
    }

    public function limitForWorkspace(int $workspaceId, string $limitKey): ?int
    {
        return $this->planForWorkspace($workspaceId)?->limitValue($limitKey);
    }

    public function hasPlan(int $workspaceId): bool
    {
        return $this->planForWorkspace($workspaceId) !== null;
    }

    public function whatsappConnectionCount(int $workspaceId): int
    {
        return WhatsappBusinessAccount::where('workspace_id', $workspaceId)->count();
    }

    public function socialConnectionCount(int $workspaceId): int
    {
        return SocialAccount::where('workspace_id', $workspaceId)->count()
            + ChannelAccount::where('workspace_id', $workspaceId)
                ->whereIn('channel', ['instagram', 'messenger'])
                ->count();
    }
}
