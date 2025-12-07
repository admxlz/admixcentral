<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class ServicesUpnpController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $upnp = [];
        $error = null;

        try {
            $response = $api->getUpnp();
            $upnp = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('services.upnp.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('services.upnp.index', compact('firewall', 'upnp', 'error'));
    }

    public function update(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateUpnp($data);
            return redirect()->route('services.upnp', $firewall)
                ->with('success', 'UPnP settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update UPnP settings: ' . $e->getMessage())->withInput();
        }
    }
}
