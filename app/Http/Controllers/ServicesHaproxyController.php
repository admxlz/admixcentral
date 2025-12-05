<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicesHaproxyController extends Controller
{
    protected function getApi(Firewall $firewall)
    {
        return new PfSenseApiService($firewall);
    }

    public function index(Firewall $firewall)
    {
        return redirect()->route('services.haproxy.settings', $firewall);
    }

    // --- Settings ---

    public function settings(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            // Adjust endpoint if necessary based on API docs.
            // Assuming /services/haproxy/settings gets global config.
            $response = $api->get('/services/haproxy/settings');
            $settings = $response['data'] ?? [];
        } catch (\Exception $e) {
            $settings = [];
            session()->flash('error', 'Failed to fetch HAProxy settings: ' . $e->getMessage());
        }

        return view('services.haproxy.index', [
            'firewall' => $firewall,
            'tab' => 'settings',
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $api->patch('/services/haproxy/settings', $request->except(['_token']));
            session()->flash('success', 'HAProxy settings updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update settings: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.settings', $firewall);
    }

    // --- Frontends ---

    public function frontends(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/haproxy/frontends');
            $frontends = $response['data'] ?? [];
        } catch (\Exception $e) {
            $frontends = [];
            session()->flash('error', 'Failed to fetch frontends: ' . $e->getMessage());
        }

        return view('services.haproxy.index', [
            'firewall' => $firewall,
            'tab' => 'frontends',
            'frontends' => $frontends,
        ]);
    }

    public function createFrontend(Firewall $firewall)
    {
        // Need backends list to select default backend
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/haproxy/backends');
            $backends = $response['data'] ?? [];
        } catch (\Exception $e) {
            $backends = [];
        }

        return view('services.haproxy.edit_frontend', [
            'firewall' => $firewall,
            'frontend' => null,
            'backends' => $backends,
        ]);
    }

    public function storeFrontend(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'required|in:active,disabled',
                'type' => 'required|in:http,tcp', // Mode
                // Add more fields as needed (externalAddr, maxconn, default_backend, etc.)
            ]);

            $api->post('/services/haproxy/frontend', $request->except(['_token']));
            session()->flash('success', 'Frontend created successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create frontend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.frontends.index', $firewall);
    }

    public function editFrontend(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $fResponse = $api->get('/services/haproxy/frontend', ['id' => $id]);
            $frontend = $fResponse['data'] ?? null;

            $bResponse = $api->get('/services/haproxy/backends');
            $backends = $bResponse['data'] ?? [];
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load frontend: ' . $e->getMessage());
            return redirect()->route('services.haproxy.frontends.index', $firewall);
        }

        return view('services.haproxy.edit_frontend', [
            'firewall' => $firewall,
            'frontend' => $frontend,
            'id' => $id,
            'backends' => $backends,
        ]);
    }

    public function updateFrontend(Request $request, Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $payload = $request->except(['_token', '_method']);
            $payload['id'] = $id;

            $api->patch('/services/haproxy/frontend', $payload);
            session()->flash('success', 'Frontend updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update frontend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.frontends.index', $firewall);
    }

    public function destroyFrontend(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $api->delete('/services/haproxy/frontend', ['id' => $id]);
            session()->flash('success', 'Frontend deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete frontend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.frontends.index', $firewall);
    }


    // --- Backends ---

    public function backends(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/haproxy/backends');
            $backends = $response['data'] ?? [];
        } catch (\Exception $e) {
            $backends = [];
            session()->flash('error', 'Failed to fetch backends: ' . $e->getMessage());
        }

        return view('services.haproxy.index', [
            'firewall' => $firewall,
            'tab' => 'backends',
            'backends' => $backends,
        ]);
    }

    public function createBackend(Firewall $firewall)
    {
        return view('services.haproxy.edit_backend', [
            'firewall' => $firewall,
            'backend' => null,
        ]);
    }

    public function storeBackend(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'connected_backend_names' => 'nullable|string', // Could be tricky, assuming string list or array
            ]);
            // API will consume payload
            $api->post('/services/haproxy/backend', $request->except(['_token']));
            session()->flash('success', 'Backend created successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create backend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.backends.index', $firewall);
    }

    public function editBackend(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/haproxy/backend', ['id' => $id]);
            $backend = $response['data'] ?? null;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load backend: ' . $e->getMessage());
            return redirect()->route('services.haproxy.backends.index', $firewall);
        }

        return view('services.haproxy.edit_backend', [
            'firewall' => $firewall,
            'backend' => $backend,
            'id' => $id,
        ]);
    }

    public function updateBackend(Request $request, Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $payload = $request->except(['_token', '_method']);
            $payload['id'] = $id;

            $api->patch('/services/haproxy/backend', $payload);
            session()->flash('success', 'Backend updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update backend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.backends.index', $firewall);
    }

    public function destroyBackend(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $api->delete('/services/haproxy/backend', ['id' => $id]);
            session()->flash('success', 'Backend deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete backend: ' . $e->getMessage());
        }
        return redirect()->route('services.haproxy.backends.index', $firewall);
    }
}
