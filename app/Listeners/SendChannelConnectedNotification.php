<?php

namespace App\Listeners;

use App\Events\ChannelConnected;
use App\Models\User;
use App\Notifications\ChannelConnectedNotification;
use Illuminate\Support\Facades\Notification;

class SendChannelConnectedNotification
{
    public function handle(ChannelConnected $event): void
    {
        $recipients = User::where('workspace_id', $event->workspaceId)->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ChannelConnectedNotification($event->channel, $event->name, $event->count),
        );
    }
}
