<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class InterfacesLaggController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $laggs = [];
        $error = null;

        try {
            $response = $api->getLaggs();
            $laggs = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('interfaces.laggs.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('interfaces.laggs.index', compact('firewall', 'laggs', 'error'));
    }

    public function create(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];

        try {
            // Need physical ports
            $response = $api->getAvailableInterfaces();
            $interfaces = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Best effort
        }

        return view('interfaces.laggs.create', compact('firewall', 'interfaces'));
    }

    public function store(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->createLagg($data);
            return redirect()->route('interfaces.laggs.index', $firewall)
                ->with('success', 'LAGG created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create LAGG: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        $lagg = null;
        $interfaces = [];

        try {
            $response = $api->getLagg($id);
            $lagg = $response['data'] ?? null;

            $ifResponse = $api->getAvailableInterfaces();
            $interfaces = $ifResponse['data'] ?? [];

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch LAGG data: ' . $e->getMessage());
        }

        if (!$lagg) {
            return redirect()->route('interfaces.laggs.index', $firewall)
                ->with('error', 'LAGG not found.');
        }

        return view('interfaces.laggs.edit', compact('firewall', 'lagg', 'interfaces', 'id'));
    }

    public function update(Firewall $firewall, string $id, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateLagg($id, $data);
            return redirect()->route('interfaces.laggs.index', $firewall)
                ->with('success', 'LAGG updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update LAGG: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->deleteLagg($id);
            return redirect()->route('interfaces.laggs.index', $firewall)
                ->with('success', 'LAGG deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete LAGG: ' . $e->getMessage());
        }
    }
}
