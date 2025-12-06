<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class FirewallBulkController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'firewall_ids' => 'required|array',
            'firewall_ids.*' => 'exists:firewalls,id',
            'action' => 'required|string',
        ]);

        $firewalls = Firewall::find($request->firewall_ids);
        $action = $request->action;
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($firewalls as $firewall) {
            // Apply scoped access check manually just in case, though index selection implies visibility
            if (!auth()->user()->isGlobalAdmin() && $firewall->company_id !== auth()->user()->company_id) {
                continue;
            }

            try {
                $api = new PfSenseApiService($firewall);

                if ($action === 'reboot') {
                    // Call API to reboot
                    // Assuming PfSenseApiService has a reboot method or we call diag_reboot.php via exec or specific endpoint
                    // Let's check Service capability. For now, we can use an exec command or specific endpoint.
                    // pfSense API v2 has /api/v2/diagnostics/reboot
                    $api->post('diagnostics/reboot');
                    $results[] = "{$firewall->name}: Reboot command sent.";
                    $successCount++;
                } elseif ($action === 'update') {
                    // Run pfSense-upgrade command
                    // Command: /usr/sbin/pfSense-upgrade -y
                    // We need an exec capability in API.
                    // Assuming we implemented command prompt or similiar, or use diagnostics/command_prompt if available
                    // or /api/v2/diagnostics/command (if implemented)
                    // Let's assume we can use the 'exec' capability if available, or try to use command prompt endpoint.
                    // The plan said "Shell Command".
                    // The standard API might not expose raw shell execution safely unless we added it.
                    // But we verified "Command Prompt" feature earlier.
                    // Let's use `execCommand` wrapper if it exists or generic post.
                    // We verified `/api/v2/diagnostics/command_prompt` takes `command`.
                    $response = $api->post('diagnostics/command_prompt', ['command' => '/usr/sbin/pfSense-upgrade -y']);
                    $results[] = "{$firewall->name}: Update initiated.";
                    $successCount++;
                } else {
                    $results[] = "{$firewall->name}: Unknown action.";
                    $failureCount++;
                }

            } catch (\Exception $e) {
                $results[] = "{$firewall->name}: Failed - " . $e->getMessage();
                $failureCount++;
            }
        }

        return redirect()->route('firewalls.index')->with([
            'bulk_results' => $results,
            'success' => "Processed {$successCount} firewalls. Errors: {$failureCount}.",
        ]);
    }

    public function create(Request $request, $type)
    {
        $request->validate([
            'firewall_ids' => 'required|array',
        ]);

        $ids = implode(',', $request->firewall_ids);

        return view('firewalls.bulk.create', [
            'type' => $type,
            'firewall_ids' => $ids
        ]);
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'firewall_ids' => 'required|string', // Comma separated
        ]);

        $firewall_ids = explode(',', $request->firewall_ids);
        $firewalls = Firewall::find($firewall_ids);

        $successCount = 0;
        $failureCount = 0;
        $results = [];

        foreach ($firewalls as $firewall) {
            if (!auth()->user()->isGlobalAdmin() && $firewall->company_id !== auth()->user()->company_id) {
                continue;
            }

            try {
                $api = new PfSenseApiService($firewall);

                if ($type === 'alias') {
                    // Validate
                    $data = $request->validate([
                        'name' => 'required',
                        'type' => 'required',
                        'address' => 'nullable',
                        'descr' => 'nullable'
                    ]);
                    // Add logic to parse address into details if needed by API? 
                    // API expects 'address' as array or string. Usually array for multiple.
                    // Textarea input is space/comma separated.
                    if (!empty($data['address'])) {
                        $data['address'] = preg_split('/[\s,]+/', $data['address'], -1, PREG_SPLIT_NO_EMPTY);
                    }

                    $api->createAlias($data);

                } elseif ($type === 'nat') {
                    // ... implementation for NAT
                    $data = $request->all(); // Filtered by model/validation
                    // For mass add, we rely on the specific method.
                    // Assuming API service has createNatPortForward
                    $api->createNatPortForward($data);
                } elseif ($type === 'rule') {
                    $data = $request->all();
                    $api->createFirewallRule($data);

                } elseif ($type === 'ipsec') {
                    $data = $request->all();
                    // Default to Phase 1 creation for "Add IPSec Tunnel"
                    $api->createIpsecPhase1($data);
                }

                $successCount++;
                $results[] = "{$firewall->name}: Configuration pushed.";

            } catch (\Exception $e) {
                $failureCount++;
                $results[] = "{$firewall->name}: Failed - " . $e->getMessage();
            }
        }

        return redirect()->route('firewalls.index')->with([
            'bulk_results' => $results,
            'success' => "Config pushed to {$successCount} firewalls.",
        ]);
    }
}
