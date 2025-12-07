<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class InterfacesGreController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $gres = [];
        $error = null;

        try {
            $response = $api->getGres();
            $gres = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('interfaces.gre.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('interfaces.gre.index', compact('firewall', 'gres', 'error'));
    }

    public function create(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];

        try {
            // Need parent interface options for tunnel source
            $response = $api->getInterfaces();
            $interfaces = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Best effort
        }

        return view('interfaces.gre.create', compact('firewall', 'interfaces'));
    }

    public function store(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->createGre($data);
            return redirect()->route('interfaces.gre.index', $firewall)
                ->with('success', 'GRE Tunnel created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create GRE Tunnel: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        $gre = null;
        $interfaces = [];

        try {
            $response = $api->getGre($id);
            $gre = $response['data'] ?? null;

            $ifResponse = $api->getInterfaces();
            $interfaces = $ifResponse['data'] ?? []; // For parent interface selection

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch GRE data: ' . $e->getMessage());
        }

        if (!$gre) {
            return redirect()->route('interfaces.gre.index', $firewall)
                ->with('error', 'GRE Tunnel not found.');
        }

        return view('interfaces.gre.edit', compact('firewall', 'gre', 'interfaces', 'id'));
    }

    public function update(Firewall $firewall, string $id, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateGre($id, $data);
            return redirect()->route('interfaces.gre.index', $firewall)
                ->with('success', 'GRE Tunnel updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update GRE Tunnel: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->deleteGre($id);
            return redirect()->route('interfaces.gre.index', $firewall)
                ->with('success', 'GRE Tunnel deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete GRE Tunnel: ' . $e->getMessage());
        }
    }
}
