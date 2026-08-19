<?php

namespace App\Notifications;

use App\Notifications\Channels\OneSignalChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ChannelConnectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $channel,
        public readonly string $name,
        public readonly int $count = 1,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (app(OneSignalChannel::class)->isConfigured()) {
            $channels[] = OneSignalChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'channel_connected',
            'channel' => $this->channel,
            'name' => $this->name,
            'count' => $this->count,
            'message' => $this->count > 1
                ? "{$this->count} {$this->name} accounts connected."
                : "{$this->name} account connected.",
            'url' => route('client.dashboard'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toOneSignal(object $notifiable): array
    {
        return [
            'title' => 'Channel connected',
            'body' => $this->count > 1
                ? "{$this->count} {$this->name} accounts connected."
                : "{$this->name} account connected.",
            'url' => route('client.dashboard'),
        ];
    }
}
