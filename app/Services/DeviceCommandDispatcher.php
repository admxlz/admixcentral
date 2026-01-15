<?php

namespace App\Services;

use App\Events\DeviceCommandEvent;
use App\Models\Firewall;
use Illuminate\Support\Facades\Log;

class DeviceCommandDispatcher
{
    protected WebSocketConnectionManager $connectionManager;
    protected PfSenseApiService $apiService;

    public function __construct(WebSocketConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Send a command to a device via WebSocket or fallback to REST API.
     *
     * @param Firewall $firewall The target firewall device
     * @param string $command Command type (e.g., 'get_status', 'apply_config', 'reboot')
     * @param array $payload Command payload/parameters
     * @return array Response from device
     */
    public function sendCommand(Firewall $firewall, string $command, array $payload = []): array
    {
        // Check if WebSocket is enabled
        if (!config('app.websocket_enabled', false)) {
            Log::info("WebSocket disabled, using REST API fallback for firewall {$firewall->id}");
            return $this->sendViaRest($firewall, $command, $payload);
        }

        // Check if device is online via WebSocket
        if (!$this->connectionManager->isDeviceOnline($firewall)) {
            Log::info("Device offline via WebSocket, using REST API fallback for firewall {$firewall->id}");
            return $this->sendViaRest($firewall, $command, $payload);
        }

        // Try sending via WebSocket
        try {
            return $this->sendViaWebSocket($firewall, $command, $payload);
        } catch (\Exception $e) {
            Log::error("WebSocket command failed for firewall {$firewall->id}: {$e->getMessage()}");
            Log::info("Falling back to REST API for firewall {$firewall->id}");
            return $this->sendViaRest($firewall, $command, $payload);
        }
    }

    /**
     * Send command via WebSocket.
     */
    protected function sendViaWebSocket(Firewall $firewall, string $command, array $payload): array
    {
        $connection = $this->connectionManager->getActiveConnection($firewall);

        if (!$connection) {
            throw new \Exception('No active WebSocket connection');
        }

        // Generate unique request ID for tracking
        $requestId = uniqid('cmd_', true);

        // Broadcast command to device channel
        broadcast(new DeviceCommandEvent($firewall, $command, $payload, $requestId));

        Log::info("Command sent via WebSocket to firewall {$firewall->id}", [
            'command' => $command,
            'request_id' => $requestId,
        ]);

        // TODO: Implement response waiting mechanism
        // For now, return success indicator
        return [
            'success' => true,
            'method' => 'websocket',
            'request_id' => $requestId,
            'message' => 'Command sent via WebSocket',
        ];
    }

    /**
     * Send command via REST API (fallback).
     */
    protected function sendViaRest(Firewall $firewall, string $command, array $payload): array
    {
        $api = new PfSenseApiService($firewall);

        // Map command to REST API endpoint
        $response = $this->executeRestCommand($api, $command, $payload);

        Log::info("Command sent via REST API to firewall {$firewall->id}", [
            'command' => $command,
        ]);

        return [
            'success' => true,
            'method' => 'rest',
            'data' => $response,
        ];
    }

    /**
     * Execute REST API command based on command type.
     */
    protected function executeRestCommand(PfSenseApiService $api, string $command, array $payload): mixed
    {
        // Map commands to API methods
        return match ($command) {
            'get_status' => $api->getSystemStatus(),
            'get_interfaces' => $api->getInterfaces(),
            'get_gateways' => $api->getGateways(),
            'get_firewall_rules' => $api->getFirewallRules(),
            'apply_changes' => $api->applyChanges(),
            default => throw new \InvalidArgumentException("Unknown command: {$command}"),
        };
    }

    /**
     * Check if device is available via WebSocket.
     */
    public function isWebSocketAvailable(Firewall $firewall): bool
    {
        if (!config('app.websocket_enabled', false)) {
            return false;
        }

        return $this->connectionManager->isDeviceOnline($firewall);
    }
}
