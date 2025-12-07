<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class InterfacesWirelessController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $wireless = [];
        $error = null;

        try {
            $response = $api->getWireless();
            $wireless = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('interfaces.wireless.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('interfaces.wireless.index', compact('firewall', 'wireless', 'error'));
    }

    public function create(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];

        try {
            // Need parent interface options if cloning or similar. 
            // For wireless, often need physical wireless card.
            $response = $api->getInterfaces();
            $interfaces = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Best effort
        }

        return view('interfaces.wireless.create', compact('firewall', 'interfaces'));
    }

    public function store(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->createWireless($data);
            return redirect()->route('interfaces.wireless.index', $firewall)
                ->with('success', 'Wireless Interface created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create Wireless Interface: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        $wireless = null;
        $interfaces = [];

        try {
            $response = $api->getWirelessDevice($id);
            $wireless = $response['data'] ?? null;

            $ifResponse = $api->getInterfaces();
            $interfaces = $ifResponse['data'] ?? [];

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch Wireless data: ' . $e->getMessage());
        }

        if (!$wireless) {
            return redirect()->route('interfaces.wireless.index', $firewall)
                ->with('error', 'Wireless Interface not found.');
        }

        return view('interfaces.wireless.edit', compact('firewall', 'wireless', 'interfaces', 'id'));
    }

    public function update(Firewall $firewall, string $id, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateWireless($id, $data);
            return redirect()->route('interfaces.wireless.index', $firewall)
                ->with('success', 'Wireless Interface updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update Wireless Interface: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->deleteWireless($id);
            return redirect()->route('interfaces.wireless.index', $firewall)
                ->with('success', 'Wireless Interface deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete Wireless Interface: ' . $e->getMessage());
        }
    }
}
