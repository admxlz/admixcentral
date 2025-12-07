<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;

class DiagnosticsRebootController extends Controller
{
    public function index(Firewall $firewall)
    {
        return view('diagnostics.reboot.index', compact('firewall'));
    }

    public function reboot(Firewall $firewall)
    {
        $api = new PfSenseApiService($firewall);

        try {
            // Using the new diagnosticsReboot method we identified
            $api->diagnosticsReboot();
            return redirect()->route('firewall.dashboard', $firewall)->with('success', 'System is rebooting. It may take a few minutes to come back online.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initiate reboot: ' . $e->getMessage());
        }
    }
}
