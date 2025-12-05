<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VpnOpenVpnController extends Controller
{
    public function servers(\App\Models\Firewall $firewall)
    {
        $api = new \App\Services\PfSenseApiService($firewall);
        $servers = [];
        try {
            $servers = $api->getOpenVpnServers()['data'] ?? [];
        } catch (\Exception $e) {
            // Log error or handle it
        }
        return view('vpn.openvpn.servers', compact('firewall', 'servers'));
    }

    public function clients(\App\Models\Firewall $firewall)
    {
        $api = new \App\Services\PfSenseApiService($firewall);
        $clients = [];
        try {
            $clients = $api->getOpenVpnClients()['data'] ?? [];
        } catch (\Exception $e) {
            // Log error or handle it
        }
        return view('vpn.openvpn.clients', compact('firewall', 'clients'));
    }

    public function createServer(\App\Models\Firewall $firewall)
    {
        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $interfaces = $api->getInterfaces()['data'] ?? [];
            $cas = $api->getCertificateAuthorities()['data'] ?? [];
            $certs = $api->getCertificates()['data'] ?? [];

            return view('vpn.openvpn.edit-server', compact('firewall', 'interfaces', 'cas', 'certs'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load OpenVPN server creation form: ' . $e->getMessage());
        }
    }

    public function storeServer(Request $request, \App\Models\Firewall $firewall)
    {
        $data = $request->except(['_token']);

        // Handle checkboxes
        $data['gwredir'] = $request->has('gwredir') ? 'yes' : 'no';
        $data['gwredir6'] = $request->has('gwredir6') ? 'yes' : 'no';
        $data['dynamic_ip'] = $request->has('dynamic_ip') ? 'yes' : 'no';
        $data['topology_subnet'] = $request->has('topology_subnet') ? 'yes' : 'no';

        // Handle array inputs
        if (isset($data['data_ciphers'])) {
            $data['data_ciphers'] = implode(',', $data['data_ciphers']);
        }

        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $api->createOpenVpnServer($data);
            return redirect()->route('firewall.vpn.openvpn.servers', $firewall)->with('success', 'OpenVPN server created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create OpenVPN server: ' . $e->getMessage());
        }
    }

    public function editServer(\App\Models\Firewall $firewall, $id)
    {
        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $server = $api->getOpenVpnServer($id)['data'] ?? [];
            $interfaces = $api->getInterfaces()['data'] ?? [];
            $cas = $api->getCertificateAuthorities()['data'] ?? [];
            $certs = $api->getCertificates()['data'] ?? [];

            return view('vpn.openvpn.edit-server', compact('firewall', 'server', 'interfaces', 'cas', 'certs', 'id'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load OpenVPN server for editing: ' . $e->getMessage());
        }
    }

    public function updateServer(Request $request, \App\Models\Firewall $firewall, $id)
    {
        $data = $request->except(['_token', '_method']);

        // Handle checkboxes
        $data['gwredir'] = $request->has('gwredir') ? 'yes' : 'no';
        $data['gwredir6'] = $request->has('gwredir6') ? 'yes' : 'no';
        $data['dynamic_ip'] = $request->has('dynamic_ip') ? 'yes' : 'no';
        $data['topology_subnet'] = $request->has('topology_subnet') ? 'yes' : 'no';

        // Handle array inputs
        if (isset($data['data_ciphers'])) {
            $data['data_ciphers'] = implode(',', $data['data_ciphers']);
        }

        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $api->updateOpenVpnServer($id, $data);
            return redirect()->route('firewall.vpn.openvpn.servers', $firewall)->with('success', 'OpenVPN server updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update OpenVPN server: ' . $e->getMessage());
        }
    }

    public function destroyServer(\App\Models\Firewall $firewall, $id)
    {
        try {
            $api = new \App\Services\PfSenseApiService($firewall);
            $api->deleteOpenVpnServer($id);
            return back()->with('success', 'OpenVPN server deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete OpenVPN server: ' . $e->getMessage());
        }
    }
}
