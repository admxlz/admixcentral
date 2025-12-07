<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class DiagnosticsPacketCaptureController extends Controller
{
    public function index(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        $interfaces = [];
        $status = null;
        $error = null;

        try {
            $response = $api->getInterfaces();
            $interfaces = $response['data'] ?? [];

            // Check capture status if API supports it
            $status = $api->getPacketCapture();
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return view('diagnostics.packet_capture.unsupported', compact('firewall'));
            }
            $error = $e->getMessage();
        }

        return view('diagnostics.packet_capture.index', compact('firewall', 'interfaces', 'status', 'error'));
    }

    public function start(Firewall $firewall, Request $request)
    {
        $api = new PfSenseApiService($firewall);
        $data = $request->except('_token');

        try {
            $api->startPacketCapture($data);
            return back()->with('success', 'Packet Capture started.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start capture: ' . $e->getMessage());
        }
    }

    public function stop(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);
        try {
            $api->stopPacketCapture();
            return back()->with('success', 'Packet Capture stopped. Check console or file list for pcap.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to stop capture: ' . $e->getMessage());
        }
    }
}
