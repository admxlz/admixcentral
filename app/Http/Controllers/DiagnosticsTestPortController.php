<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class DiagnosticsTestPortController extends Controller
{
    public function index(Firewall $firewall)
    {
        return view('diagnostics.test_port.index', compact('firewall'));
    }

    public function test(Firewall $firewall, Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'proto' => 'nullable|string|in:tcp,udp',
        ]);

        $api = new PfSenseApiService($firewall);
        $result = null;

        try {
            $response = $api->testPort($request->all());
            $result = $response['data'] ?? 'Success'; // Assuming simple success message or structured data
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('diagnostics.test_port.unsupported', compact('firewall'));
            }
            $result = 'Failed: ' . $e->getMessage();
        }

        return view('diagnostics.test_port.index', compact('firewall', 'result'));
    }
}
