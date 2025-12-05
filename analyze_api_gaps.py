import json
import os

def analyze_gaps():
    try:
        with open('openapi.json', 'r') as f:
            data = json.load(f)
    except FileNotFoundError:
        print("Error: openapi.json not found.")
        return

    api_endpoints = {}
    for path, methods in data['paths'].items():
        for method, op in methods.items():
            tags = op.get('tags', ['Uncategorized'])
            tag = tags[0] if tags else 'Uncategorized'
            summary = op.get('summary', 'No summary')
            
            if tag not in api_endpoints:
                api_endpoints[tag] = []
            api_endpoints[tag].append({
                'method': method.upper(),
                'path': path,
                'summary': summary
            })

    # Manual mapping of implemented features based on routes/web.php analysis
    implemented_features = {
        'System': ['Advanced', 'General Setup', 'High Availability Sync', 'Certificate Manager', 'User Manager', 'Package Manager', 'Routing', 'Gateway Groups', 'Static Routes', 'Gateways'],
        'Interfaces': ['Assignments', 'VLANs', 'Interface Configuration'], # Broadly covered
        'Firewall': ['Rules', 'Aliases', 'NAT One-to-One', 'NAT Outbound', 'NAT Port Forward', 'Schedules', 'Traffic Shaper', 'Virtual IPs', 'Copy Rules'],
        'Services': ['Captive Portal', 'DHCP Relay', 'DHCP Server', 'DNS Forwarder', 'DNS Resolver', 'Dynamic DNS', 'IGMP Proxy', 'NTP', 'PPPoE Server', 'Router Advertisement', 'SNMP', 'UPnP', 'Wake-on-LAN', 'OpenVPN Server', 'OpenVPN Client'], # OpenVPN is under VPN in routes but might be Services in API
        'VPN': ['IPsec', 'OpenVPN', 'L2TP', 'WireGuard'],
        'Status': ['Captive Portal', 'CARP', 'DHCP Leases', 'Filter Reload', 'Gateways', 'Interfaces', 'IPsec', 'Monitoring', 'NTP', 'OpenVPN', 'Queues', 'Services', 'System Logs', 'Traffic Graph', 'UPnP', 'WireGuard'], 
        'Diagnostics': ['ARP Table', 'Authentication', 'Backup & Restore', 'Command Prompt', 'DNS Lookup', 'Edit File', 'Factory Defaults', 'Halt System', 'Limiter Info', 'NDP Table', 'Packet Capture', 'pfInfo', 'pfTop', 'Ping', 'Reboot', 'Routes', 'SMART Status', 'Sockets', 'States', 'System Activity', 'Tables', 'Test Port', 'Traceroute']
    }

    # Normalize comparison (simple substring check for now)
    missing = {}
    
    print("# API vs GUI Gap Analysis Report\n")
    
    for tag, endpoints in api_endpoints.items():
        # Clean tag name to match our implemented keys
        db_tag = tag.replace(' ', '')
        
        # Check against implemented categories
        # This is a heuristic; we iterate tags from API
        
        print(f"## Category: {tag}")
        
        for ep in endpoints:
            is_implemented = False
            # Check if this endpoint's summary or path loosely matches our known implemented list
            # detailed check would require parsing routes file, but we can do a quick visual check 
            # or rely on the fact that I just read routes/web.php
            
            # For the purpose of this script, let's just dump ALL API endpoints
            # and I (the agent) will manually flag the missing ones in the final report 
            # because mapping 1:1 programmatically is error prone without a strict naming convention.
            print(f"- [{ep['method']}] {ep['path']} : {ep['summary']}")

if __name__ == "__main__":
    analyze_gaps()
