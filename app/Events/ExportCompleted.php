<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function broadcastOn(): array
    {
        return ['exports'];
    }

    public function broadcastAs(): string
    {
        return 'ExportCompleted';
    }
}
