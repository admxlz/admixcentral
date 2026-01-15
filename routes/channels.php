<?php

use App\Models\Firewall;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Device channel - for sending commands to devices
Broadcast::channel('device.{firewallId}', function ($user, $firewallId) {
    // This channel is for devices only, not web users
    // Authentication is handled separately in the WebSocket controller
    // For now, we'll allow it and rely on device authentication
    return true;
});

// Firewall dashboard channel - for real-time updates to users
Broadcast::channel('firewall.{firewallId}', function ($user, $firewallId) {
    $firewall = Firewall::find($firewallId);

    if (!$firewall) {
        return false;
    }

    // Global admins can access any firewall
    if ($user->isGlobalAdmin()) {
        return true;
    }

    // Company admins can only access their company's firewalls
    return $user->company_id === $firewall->company_id;
});
