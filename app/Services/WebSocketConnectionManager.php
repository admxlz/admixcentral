<?php

namespace App\Services;

use App\Models\DeviceConnection;
use App\Models\Firewall;
use Illuminate\Support\Str;

class WebSocketConnectionManager
{
    /**
     * Register a new device connection.
     */
    public function registerConnection(
        Firewall $firewall,
        string $socketId,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): DeviceConnection {
        // Disconnect any existing active connections for this firewall
        $this->disconnectExistingConnections($firewall);

        // Create new connection
        $connection = DeviceConnection::create([
            'firewall_id' => $firewall->id,
            'connection_id' => Str::uuid()->toString(),
            'socket_id' => $socketId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'connected_at' => now(),
            'last_ping_at' => now(),
        ]);

        return $connection;
    }

    /**
     * Update the heartbeat timestamp for a connection.
     */
    public function updateHeartbeat(string $connectionId): bool
    {
        $connection = DeviceConnection::where('connection_id', $connectionId)
            ->active()
            ->first();

        if (!$connection) {
            return false;
        }

        $connection->updateHeartbeat();
        return true;
    }

    /**
     * Mark a device connection as disconnected.
     */
    public function disconnectDevice(string $connectionId): bool
    {
        $connection = DeviceConnection::where('connection_id', $connectionId)
            ->active()
            ->first();

        if (!$connection) {
            return false;
        }

        $connection->markDisconnected();
        return true;
    }

    /**
     * Disconnect all existing active connections for a firewall.
     * This ensures only one active connection per firewall.
     */
    protected function disconnectExistingConnections(Firewall $firewall): void
    {
        DeviceConnection::where('firewall_id', $firewall->id)
            ->active()
            ->update(['disconnected_at' => now()]);
    }

    /**
     * Get the active connection for a firewall.
     */
    public function getActiveConnection(Firewall $firewall): ?DeviceConnection
    {
        return $firewall->activeConnection;
    }

    /**
     * Check if a device is currently online via WebSocket.
     */
    public function isDeviceOnline(Firewall $firewall): bool
    {
        $connection = $this->getActiveConnection($firewall);

        if (!$connection) {
            return false;
        }

        // Check if connection is stale (no heartbeat in last 2 minutes)
        return !$connection->isStale(2);
    }

    /**
     * Clean up stale connections (no heartbeat in specified minutes).
     */
    public function cleanupStaleConnections(int $minutes = 2): int
    {
        $staleConnections = DeviceConnection::active()
            ->stale($minutes)
            ->get();

        foreach ($staleConnections as $connection) {
            $connection->markDisconnected();
        }

        return $staleConnections->count();
    }

    /**
     * Get connection statistics.
     */
    public function getConnectionStats(): array
    {
        return [
            'total_connections' => DeviceConnection::count(),
            'active_connections' => DeviceConnection::active()->count(),
            'disconnected_connections' => DeviceConnection::whereNotNull('disconnected_at')->count(),
            'stale_connections' => DeviceConnection::active()->stale(2)->count(),
        ];
    }
}
