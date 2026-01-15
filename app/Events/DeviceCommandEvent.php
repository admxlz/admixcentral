<?php

namespace App\Events;

use App\Models\Firewall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceCommandEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Firewall $firewall;
    public string $command;
    public array $payload;
    public string $requestId;

    /**
     * Create a new event instance.
     */
    public function __construct(Firewall $firewall, string $command, array $payload, string $requestId)
    {
        $this->firewall = $firewall;
        $this->command = $command;
        $this->payload = $payload;
        $this->requestId = $requestId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('device.' . $this->firewall->id);
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'command' => $this->command,
            'payload' => $this->payload,
            'request_id' => $this->requestId,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'device.command';
    }
}
