<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class ServicesCaptivePortalController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $zones = [];
        $error = null;

        try {
            $response = $api->getCaptivePortalZones();
            $zones = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('services.captive_portal.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('services.captive_portal.index', compact('firewall', 'zones', 'error'));
    }
}
