<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class ServicesNtpController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $ntp = [];
        $error = null;

        try {
            $response = $api->getNtp();
            $ntp = $response['data'] ?? [];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('services.ntp.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('services.ntp.index', compact('firewall', 'ntp', 'error'));
    }

    public function update(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except(['_token', '_method']);

        try {
            $api->updateNtp($data);
            return redirect()->route('services.ntp', $firewall)
                ->with('success', 'NTP settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update NTP settings: ' . $e->getMessage())->withInput();
        }
    }
}
