<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firewall_id')->constrained()->onDelete('cascade');
            $table->string('connection_id')->unique()->comment('Unique WebSocket connection identifier');
            $table->string('socket_id')->nullable()->comment('Pusher socket ID');
            $table->string('ip_address', 45)->nullable()->comment('Device IP address (IPv4 or IPv6)');
            $table->text('user_agent')->nullable()->comment('Device user agent string');
            $table->timestamp('connected_at')->useCurrent()->comment('When device connected');
            $table->timestamp('last_ping_at')->nullable()->comment('Last heartbeat/ping timestamp');
            $table->timestamp('disconnected_at')->nullable()->comment('When device disconnected');
            $table->timestamps();

            // Indexes for performance
            $table->index('firewall_id');
            $table->index('connection_id');
            $table->index(['firewall_id', 'disconnected_at']); // For finding active connections
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_connections');
    }
};
