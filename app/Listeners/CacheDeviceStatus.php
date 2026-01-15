<?php

namespace App\Listeners;

use App\Events\DeviceStatusUpdateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheDeviceStatus
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DeviceStatusUpdateEvent $event): void
    {
        try {
            Log::info("Caching status update for firewall: " . $event->firewall->id);

            $statusData = $event->statusData;

            // Store payload with timestamp for freshness checks
            $payload = [
                'data' => $statusData,
                'ts' => now()->timestamp,
                'received_at' => now()->toIso8601String(),
            ];

            // Cache for 24 hours - Controller decides if it's "fresh" enough
            Cache::put('firewall_status_' . $event->firewall->id, $payload, now()->addDay());

        } catch (\Exception $e) {
            Log::error("Failed to cache device status: " . $e->getMessage());
        }
    }
}
