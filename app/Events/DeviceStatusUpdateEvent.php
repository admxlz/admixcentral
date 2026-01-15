<?php

namespace App\Events;

use App\Models\Firewall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Firewall $firewall;
    public array $statusData;

    /**
     * Create a new event instance.
     */
    public function __construct(Firewall $firewall, array $statusData)
    {
        $this->firewall = $firewall;
        $this->statusData = $statusData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Broadcast to firewall dashboard channel so users can see real-time updates
        return new PrivateChannel('firewall.' . $this->firewall->id);
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'firewall_id' => $this->firewall->id,
            'status' => $this->statusData,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'firewall.status.update';
    }
}
