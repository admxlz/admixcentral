<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirewallScheduleController extends Controller
{
    public function index(Firewall $firewall)
    {
        try {
            $api = new PfSenseApiService($firewall);
            $response = $api->getSchedules();
            $schedules = $response['data'] ?? [];

            return view('firewall.schedules.index', compact('firewall', 'schedules'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch schedules: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch schedules: ' . $e->getMessage());
        }
    }

    public function create(Firewall $firewall)
    {
        return view('firewall.schedules.create', compact('firewall'));
    }

    public function store(Request $request, Firewall $firewall)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'descr' => 'nullable|string',
            'month' => 'nullable|string',
            'day' => 'nullable|string',
            'hour' => 'nullable|string',
        ]);

        // Construct timerange
        $timerange = [];
        if ($request->filled('month') || $request->filled('day') || $request->filled('hour')) {
            $timerange[] = [
                'month' => array_map('trim', explode(',', $request->input('month', 'all'))),
                'day' => array_map('trim', explode(',', $request->input('day', 'all'))),
                'hour' => array_map('trim', explode(',', $request->input('hour', '0:00-23:59'))),
                'rangedescr' => 'Created via AdmixCentral',
            ];
        } else {
            // Default time range if none provided, to satisfy API requirement
            $timerange[] = [
                'month' => ['all'],
                'day' => ['all'],
                'hour' => ['0:00-23:59'],
                'rangedescr' => 'Default All Day',
            ];
        }

        $data = [
            'name' => $validated['name'],
            'descr' => $validated['descr'] ?? '',
            'timerange' => $timerange,
        ];

        try {
            $api = new PfSenseApiService($firewall);
            $api->createSchedule($data);

            return redirect()->route('firewall.schedules.index', $firewall)
                ->with('success', 'Schedule created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    public function edit(Firewall $firewall, string $id)
    {
        try {
            $api = new PfSenseApiService($firewall);
            $response = $api->getSchedules();
            $schedules = $response['data'] ?? [];
            $schedule = collect($schedules)->firstWhere('id', $id);

            if (!$schedule) {
                return back()->with('error', 'Schedule not found.');
            }

            return view('firewall.schedules.edit', compact('firewall', 'schedule'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch schedule: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Firewall $firewall, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'descr' => 'nullable|string',
            'timerange' => 'nullable|array',
        ]);

        try {
            $api = new PfSenseApiService($firewall);
            $api->updateSchedule((int) $id, $validated);

            return redirect()->route('firewall.schedules.index', $firewall)
                ->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    public function destroy(Firewall $firewall, string $id)
    {
        try {
            $api = new PfSenseApiService($firewall);
            $api->deleteSchedule((int) $id);

            return back()->with('success', 'Schedule deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }
}
