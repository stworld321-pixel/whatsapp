<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Social\Models\SocialAccount;
use App\Modules\Whatsapp\Models\WhatsappBusinessAccount;

class PlanLimitService
{
    public function planForWorkspace(int $workspaceId): ?Plan
    {
        $workspace = Workspace::with('client')->find($workspaceId);

        $clientPlan = $workspace?->client?->effectivePlan();
        if ($clientPlan) {
            return $clientPlan;
        }

        if (! $workspace) {
            return null;
        }

        $userPlan = Subscription::query()
            ->whereIn('user_id', $workspace->users()->select('id'))
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->with('plan')
            ->orderByDesc('id')
            ->first()?->plan;

        return $userPlan;
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
