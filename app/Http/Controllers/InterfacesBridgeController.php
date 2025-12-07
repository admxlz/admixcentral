<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class InterfacesBridgeController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $bridges = [];
        $error = null;

        try {
            $response = $api->getBridges();
            $bridges = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Check if API endpoint is missing (404)
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('interfaces.bridges.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('interfaces.bridges.index', compact('firewall', 'bridges', 'error'));
    }

    public function create(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];
        try {
            // Need available interfaces to select bridge members
            // getAvailableInterfaces might return assigned ones, we might need all physical ports.
            // Using getInterfacesStatus as a proxy for available hardware ports or getAvailableInterfaces
            $response = $api->getAvailableInterfaces();
            $interfaces = $response['data'] ?? [];

            // Also might need current assignments to map names
            $assignments = $api->getInterfaces();
            // Merge or pass both
        } catch (\Exception $e) {
            // best effort
        }

        return view('interfaces.bridges.create', compact('firewall', 'interfaces'));
    }

    public function store(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->createBridge($data);
            return redirect()->route('interfaces.bridges.index', $firewall)
                ->with('success', 'Bridge created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create bridge: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        $bridge = null;
        $interfaces = [];

        try {
            $response = $api->getBridge($id);
            $bridge = $response['data'] ?? null;

            $ifResponse = $api->getAvailableInterfaces();
            $interfaces = $ifResponse['data'] ?? [];
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch bridge data: ' . $e->getMessage());
        }

        if (!$bridge) {
            return redirect()->route('interfaces.bridges.index', $firewall)
                ->with('error', 'Bridge not found.');
        }

        return view('interfaces.bridges.edit', compact('firewall', 'bridge', 'interfaces', 'id'));
    }

    public function update(Firewall $firewall, string $id, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateBridge($id, $data);
            return redirect()->route('interfaces.bridges.index', $firewall)
                ->with('success', 'Bridge updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update bridge: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->deleteBridge($id);
            return redirect()->route('interfaces.bridges.index', $firewall)
                ->with('success', 'Bridge deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete bridge: ' . $e->getMessage());
        }
    }
}
