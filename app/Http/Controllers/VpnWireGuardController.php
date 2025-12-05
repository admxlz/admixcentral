<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use Illuminate\Http\Request;

class VpnWireGuardController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new \App\Services\PfSenseApiService($firewall);
        $tunnels = [];
        $peers = [];

        try {
            $tunnels = $api->getWireGuardTunnels()['data'] ?? [];
            $peers = $api->getWireGuardPeers()['data'] ?? [];
        } catch (\Exception $e) {
            // Log error
        }

        return view('vpn.wireguard.index', compact('firewall', 'tunnels', 'peers'));
    }
}
