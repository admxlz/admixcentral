import json

try:
    with open('/home/baga/Code/admixcentral/openapi.json', 'r') as f:
        data = json.load(f)
    
    paths = list(data['paths'].keys())
    ipsec_paths = [p for p in paths if 'ipsec' in p]

    # Find paths related to High Availability Sync
    sync_paths = [path for path in paths if 'sync' in path.lower() or 'xmlrpc' in path.lower() or 'carp' in path.lower()]
    print(f"Sync paths: {sync_paths}")

    if not sync_paths:
        print("No sync paths found.")
    else:
        for path in sync_paths:
            print(f"\nSchema for POST {path}:")
            try:
                post_schema = data['paths'][path]['post']['requestBody']['content']['application/json']['schema']
                print(json.dumps(post_schema, indent=2))
                
                # Check for refs
                if '$ref' in post_schema:
                    ref = post_schema['$ref']
                    component_name = ref.split('/')[-1]
                    print(f"Schema for {component_name}:")
                    component_schema = data['components']['schemas'][component_name]
                    print(json.dumps(component_schema, indent=2))
                elif 'allOf' in post_schema:
                     for item in post_schema['allOf']:
                        if '$ref' in item:
                            ref = item['$ref']
                            component_name = ref.split('/')[-1]
                            print(f"Schema for {component_name}:")
                            component_schema = data['components']['schemas'][component_name]
                            print(json.dumps(component_schema, indent=2))

            except KeyError:
                print(f"No POST schema found for {path}")
    
    with open('/home/baga/Code/admixcentral/inspect_openapi_output.txt', 'w') as out:
        out.write(f"Total paths: {len(paths)}\n")
        out.write(f"IPsec paths: {ipsec_paths}\n")
        out.write(f"Sync paths: {sync_paths}\n")
        
        if not sync_paths:
            out.write("No sync paths found.\n")
        else:
            for path in sync_paths:
                out.write(f"\nSchema for POST {path}:\n")
                try:
                    post_schema = data['paths'][path]['post']['requestBody']['content']['application/json']['schema']
                    out.write(json.dumps(post_schema, indent=2) + "\n")
                except KeyError:
                    out.write(f"No POST schema found for {path}\n")
        
        target_path = '/api/v2/vpn/ipsec/phase2'
        if target_path in data['paths']:
            if 'post' in data['paths'][target_path]:
                schema = data['paths'][target_path]['post']['requestBody']['content']['application/json']['schema']
                out.write(f"\nSchema for POST {target_path}:\n")
                out.write(json.dumps(schema, indent=2))
            else:
                out.write(f"\nNo POST method for {target_path}\n")
        
        if 'components' in data and 'schemas' in data['components'] and 'IPsecPhase2' in data['components']['schemas']:
            schema = data['components']['schemas']['IPsecPhase2']
            out.write(f"\nSchema for IPsecPhase2:\n")
            out.write(json.dumps(schema, indent=2))
        else:
            out.write("\nIPsecPhase2 schema not found in components/schemas\n")

except Exception as e:
    with open('/home/baga/Code/admixcentral/inspect_openapi_output.txt', 'w') as out:
        out.write(f"Error: {e}\n")
