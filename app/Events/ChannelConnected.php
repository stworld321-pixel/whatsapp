<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ChannelConnected
{
    use Dispatchable;

    public function __construct(
        public readonly int $workspaceId,
        public readonly string $channel,
        public readonly string $name,
        public readonly int $count = 1,
    ) {}
}
