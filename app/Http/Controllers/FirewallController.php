<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Models\Company;
use Illuminate\Http\Request;

class FirewallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $firewalls = Firewall::with('company')->get();
        } else {
            $firewalls = Firewall::where('company_id', $user->company_id)->get();
        }

        return view('firewalls.index', compact('firewalls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        return view('firewalls.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'auth_method' => 'required|in:basic,token',
            'api_key' => 'required_if:auth_method,basic|nullable|string',
            'api_secret' => 'required_if:auth_method,basic|nullable|string',
            'api_token' => 'required_if:auth_method,token|nullable|string',
            'description' => 'nullable|string',
        ]);

        // Create instance to fetch ID
        $firewall = new Firewall($validated);
        // We need to save it temporarily or handle the API call without a saved model?
        // Service requires a model.
        // Let's create it first, then try to fetch ID and update.
        // Or refrain from using the service class for the initial check?
        // The service class expects a Firewall model, but it doesn't strictly need it to be saved if we populate the properties.

        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $response = $api->get('/status/system');
            if (isset($response['data']['netgate_id'])) {
                $validated['netgate_id'] = $response['data']['netgate_id'];
            }
        } catch (\Exception $e) {
            // Log error or set a flash warning?
            // For now, proceed without ID, it will fallback to ID if we didn't override route key?
            // Wait, we OVERRODE route key. If netgate_id is null, links might break or look like /firewall//dashboard
            // We should arguably fail or generate a UUID if we can't reach it?
            // Let's just create it and maybe try to fetch later? 
            // Or better, set it to the auto-increment ID if API fails?
            // But netgate_id is string.
        }

        $firewall = Firewall::create($validated);

        // If we didn't get netgate_id, maybe set it to the ID?
        if (!$firewall->netgate_id) {
            $firewall->netgate_id = (string) $firewall->id; // Fallback
            $firewall->save();
        }

        return redirect()->route('firewalls.index')->with('success', 'Firewall created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Firewall $firewall)
    {
        return redirect()->route('firewall.dashboard', $firewall);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Firewall $firewall)
    {
        $companies = Company::all();
        return view('firewalls.edit', compact('firewall', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Firewall $firewall)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'auth_method' => 'required|in:basic,token',
            'api_key' => 'required_if:auth_method,basic|nullable|string',
            'api_secret' => 'nullable|string', // Nullable because we might be keeping existing
            'api_token' => 'required_if:auth_method,token|nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validated['auth_method'] === 'token') {
            $validated['api_key'] = null;
            $validated['api_secret'] = null;
        } else {
            $validated['api_token'] = null;

            // Handle password update for Basic Auth
            if (empty($validated['api_secret'])) {
                if (empty($firewall->api_secret) && $firewall->auth_method !== 'basic') {
                    // Switching to Basic but no password provided and no existing password
                    return back()->withErrors(['api_secret' => 'Password is required when switching to Basic Authentication.'])->withInput();
                }
                unset($validated['api_secret']);
            }
        }

        $firewall->update($validated);

        return redirect()->route('firewalls.index')->with('success', 'Firewall updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Firewall $firewall)
    {
        $firewall->delete();

        return redirect()->route('firewalls.index')->with('success', 'Firewall deleted successfully.');
    }
}
