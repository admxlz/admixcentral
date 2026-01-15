<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceConnection extends Model
{
    protected $fillable = [
        'firewall_id',
        'connection_id',
        'socket_id',
        'ip_address',
        'user_agent',
        'connected_at',
        'last_ping_at',
        'disconnected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_ping_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    /**
     * Get the firewall that owns this connection.
     */
    public function firewall(): BelongsTo
    {
        return $this->belongsTo(Firewall::class);
    }

    /**
     * Scope to get only active connections (not disconnected).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('disconnected_at');
    }

    /**
     * Scope to get stale connections (no ping in last 2 minutes).
     */
    public function scopeStale($query, int $minutes = 2)
    {
        return $query->where('last_ping_at', '<', now()->subMinutes($minutes))
            ->orWhereNull('last_ping_at');
    }

    /**
     * Check if this connection is currently active.
     */
    public function isActive(): bool
    {
        return $this->disconnected_at === null;
    }

    /**
     * Check if this connection is stale (no recent heartbeat).
     */
    public function isStale(int $minutes = 2): bool
    {
        if (!$this->last_ping_at) {
            return true;
        }

        return $this->last_ping_at->lt(now()->subMinutes($minutes));
    }

    /**
     * Mark this connection as disconnected.
     */
    public function markDisconnected(): void
    {
        $this->update(['disconnected_at' => now()]);
    }

    /**
     * Update the last ping timestamp.
     */
    public function updateHeartbeat(): void
    {
        $this->update(['last_ping_at' => now()]);
    }
}
