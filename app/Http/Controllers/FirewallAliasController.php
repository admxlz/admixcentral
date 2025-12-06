<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirewallAliasController extends Controller
{
    public function index(Firewall $firewall)
    {
        try {
            $api = new PfSenseApiService($firewall);
            $response = $api->get('/firewall/aliases');

            $aliases = $response['data'] ?? [];

            return view('firewall.aliases.index', compact('firewall', 'aliases'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch aliases: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch aliases: ' . $e->getMessage());
        }
    }

    public function create(Firewall $firewall)
    {
        $alias = [
            'name' => '',
            'type' => 'host',
            '

descr' => '',
            'address' => [''],
            'detail' => [''],
        ];

        return view('firewall.aliases.edit', compact('firewall', 'alias'));
    }

    public function store(Request $request, Firewall $firewall)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'type' => 'required|in:host,network,port,url,urltable',
            'descr' => 'nullable|string',
            'address' => 'required|array',
            'address.*' => 'required|string',
            'detail' => 'array',
            'detail.*' => 'nullable|string',
        ]);

        // Prepare data for API
        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'descr' => $validated['descr'] ?? '',
            'address' => array_values(array_filter($validated['address'])),
            'detail' => array_values(array_map(fn($d) => $d ?? '', $validated['detail'])),
        ];

        try {
            $api = new PfSenseApiService($firewall);
            $api->post('/firewall/alias', $data);
            $firewall->update(['is_dirty' => true]);

            return redirect()->route('firewall.aliases.index', $firewall)
                ->with('success', 'Alias created successfully. Please apply changes.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create alias: ' . $e->getMessage());
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        try {
            $api = new PfSenseApiService($firewall);
            // Fetch specific alias by ID
            $response = $api->get('/firewall/alias', ['id' => $id]);
            $alias = $response['data'] ?? null;

            if (!$alias) {
                return back()->with('error', 'Alias not found.');
            }

            // Parse address and detail fields
            // API might return string (space separated) or array
            $alias['address'] = is_string($alias['address'] ?? '') ? explode(' ', $alias['address']) : ($alias['address'] ?? ['']);
            $alias['detail'] = is_string($alias['detail'] ?? '') ? explode('||', $alias['detail']) : ($alias['detail'] ?? ['']);

            // Ensure arrays have same length
            $alias['address'] = array_pad($alias['address'], max(count($alias['address']), 1), '');
            $alias['detail'] = array_pad($alias['detail'], max(count($alias['detail']), 1), '');

            return view('firewall.aliases.edit', compact('firewall', 'alias'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch alias: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Firewall $firewall, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'type' => 'required|in:host,network,port,url,urltable',
            'descr' => 'nullable|string',
            'address' => 'required|array',
            'address.*' => 'required|string',
            'detail' => 'array',
            'detail.*' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'descr' => $validated['descr'] ?? '',
            'address' => array_values(array_filter($validated['address'])),
            'detail' => array_values(array_map(fn($d) => $d ?? '', $validated['detail'])),
        ];

        try {
            $api = new PfSenseApiService($firewall);
            // Use updateAlias method which uses correct PATCH logic
            $api->updateAlias($id, $data);
            $firewall->update(['is_dirty' => true]);

            return redirect()->route('firewall.aliases.index', $firewall)
                ->with('success', 'Alias updated successfully. Please apply changes.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update alias: ' . $e->getMessage());
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        Log::info("Attempting to delete alias: {$id} on firewall {$firewall->id}");
        try {
            $api = new PfSenseApiService($firewall);
            // Use deleteAlias method
            $api->deleteAlias($id);
            $firewall->update(['is_dirty' => true]);

            return back()->with('success', 'Alias deleted successfully. Please apply changes.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete alias: ' . $e->getMessage());
        }
    }


}
