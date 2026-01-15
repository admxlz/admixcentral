<?php

namespace App\Events;

use App\Models\DeviceConnection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceConnectedEvent
{
    use Dispatchable, SerializesModels;

    public DeviceConnection $connection;

    /**
     * Create a new event instance.
     */
    public function __construct(DeviceConnection $connection)
    {
        $this->connection = $connection;
    }
}
