<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class InterfacesGroupController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $groups = [];
        $error = null;

        try {
            $response = $api->get('/interface/groups');
            $groups = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Check if API endpoint is missing (404)
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('interfaces.groups.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('interfaces.groups.index', compact('firewall', 'groups', 'error'));
    }

    public function create(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];

        try {
            // Interface Groups use logical interface names (wan, lan, opt1, etc.)
            // So we need to fetch the configured interfaces, not just available physical ports.
            $response = $api->getInterfaces();
            $interfaces = $response['data'] ?? [];
        } catch (\Exception $e) {
            // best effort
        }

        return view('interfaces.groups.create', compact('firewall', 'interfaces'));
    }

    public function store(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->post('/interface/group', $data);
            return redirect()->route('interfaces.groups.index', $firewall)
                ->with('success', 'Interface Group created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create interface group: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        $group = null;
        $interfaces = [];

        try {
            $response = $api->get('/interface/group', ['id' => $id]);
            $group = $response['data'] ?? null;

            $ifResponse = $api->getInterfaces();
            $interfaces = $ifResponse['data'] ?? [];
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch group data: ' . $e->getMessage());
        }

        if (!$group) {
            return redirect()->route('interfaces.groups.index', $firewall)
                ->with('error', 'Interface Group not found.');
        }

        return view('interfaces.groups.edit', compact('firewall', 'group', 'interfaces', 'id'));
    }

    public function update(Firewall $firewall, string $id, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);
        $data['id'] = $id;

        try {
            $api->patch('/interface/group', $data);
            return redirect()->route('interfaces.groups.index', $firewall)
                ->with('success', 'Interface Group updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update interface group: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->delete('/interface/group', ['id' => $id]);
            return redirect()->route('interfaces.groups.index', $firewall)
                ->with('success', 'Interface Group deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete interface group: ' . $e->getMessage());
        }
    }
}
