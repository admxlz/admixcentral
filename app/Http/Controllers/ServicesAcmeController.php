<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicesAcmeController extends Controller
{
    protected function getApi(Firewall $firewall)
    {
        return new PfSenseApiService($firewall);
    }

    public function index(Firewall $firewall)
    {
        return redirect()->route('services.acme.certificates', ['firewall' => $firewall]);
    }

    public function certificates(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/acme/certificates');
            $certificates = $response['data'] ?? [];

            // We also need account keys to create new certs
            $keysResponse = $api->get('/services/acme/account_keys');
            $accountKeys = $keysResponse['data'] ?? [];
        } catch (\Exception $e) {
            $certificates = [];
            $accountKeys = [];
            session()->flash('error', 'Failed to fetch ACME data: ' . $e->getMessage());
        }

        return view('services.acme.index', [
            'firewall' => $firewall,
            'tab' => 'certificates',
            'certificates' => $certificates,
            'accountKeys' => $accountKeys,
        ]);
    }

    public function storeCertificate(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            // Adjust validation based on API requirements
            $data = $request->validate([
                'name' => 'required|string',
                'descr' => 'nullable|string',
                'status' => 'required|string|in:active,disabled',
                'acme_account_key' => 'required|string',
                // Add other fields as necessary based on API schema
            ]);

            // The API likely expects more specific fields for domain lists, validation methods etc.
            // For now, passing all request data (filtered) to API.
            // Using all() for flexibility during initial dev, but strict validation is better.
            $payload = $request->except(['_token']);

            $api->post('/services/acme/certificate', $payload);
            session()->flash('success', 'Certificate added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add certificate: ' . $e->getMessage());
        }

        return redirect()->route('services.acme.certificates', $firewall);
    }

    public function issueCertificate(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $id = $request->input('id');
            // Assuming endpoint is /services/acme/certificate/issue with payload {id: ...}
            $api->post('/services/acme/certificate/issue', ['id' => $id]);
            session()->flash('success', 'Certificate issuance initiated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to issue certificate: ' . $e->getMessage());
        }
        return redirect()->route('services.acme.certificates', $firewall);
    }

    public function renewCertificate(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $id = $request->input('id');
            $api->post('/services/acme/certificate/renew', ['id' => $id]);
            session()->flash('success', 'Certificate renewal initiated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to renew certificate: ' . $e->getMessage());
        }
        return redirect()->route('services.acme.certificates', $firewall);
    }

    public function destroyCertificate(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $api->delete('/services/acme/certificate', ['id' => $id]);
            session()->flash('success', 'Certificate deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete certificate: ' . $e->getMessage());
        }
        return redirect()->route('services.acme.certificates', $firewall);
    }

    public function accountKeys(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/acme/account_keys');
            $accountKeys = $response['data'] ?? [];
        } catch (\Exception $e) {
            $accountKeys = [];
            session()->flash('error', 'Failed to fetch account keys: ' . $e->getMessage());
        }

        return view('services.acme.index', [
            'firewall' => $firewall,
            'tab' => 'account_keys',
            'accountKeys' => $accountKeys,
        ]);
    }

    public function storeAccountKey(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'descr' => 'nullable|string',
                'email' => 'required|email',
                'server' => 'required|string', // e.g. 'letsencrypt-production', 'letsencrypt-staging-2'
            ]);

            $api->post('/services/acme/account_key', $request->except(['_token']));

            // Check if we need to register immediately
            if ($request->has('register') && $request->input('register') == '1') {
                // Fetch the newly created key ID? Or maybe the POST returns it.
                // This flow might need refinement after testing.
            }

            session()->flash('success', 'Account Key added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add account key: ' . $e->getMessage());
        }

        return redirect()->route('services.acme.account-keys', $firewall);
    }

    public function destroyAccountKey(Firewall $firewall, $id)
    {
        $api = $this->getApi($firewall);
        try {
            $api->delete('/services/acme/account_key', ['id' => $id]);
            session()->flash('success', 'Account Key deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete account key: ' . $e->getMessage());
        }
        return redirect()->route('services.acme.account-keys', $firewall);
    }

    public function settings(Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $response = $api->get('/services/acme/settings');
            $settings = $response['data'] ?? [];
        } catch (\Exception $e) {
            $settings = [];
            session()->flash('error', 'Failed to fetch ACME settings: ' . $e->getMessage());
        }

        return view('services.acme.index', [
            'firewall' => $firewall,
            'tab' => 'settings',
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request, Firewall $firewall)
    {
        $api = $this->getApi($firewall);
        try {
            $api->patch('/services/acme/settings', $request->except(['_token']));
            session()->flash('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update settings: ' . $e->getMessage());
        }
        return redirect()->route('services.acme.settings', $firewall);
    }
}
