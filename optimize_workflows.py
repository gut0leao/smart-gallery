#!/usr/bin/env python3
import re

def optimize_workflow_file(filename):
    print(f"Optimizing {filename}...")
    
    with open(filename, 'r') as f:
        content = f.read()
    
    # Pattern to match the entire Load Infrastructure Information section
    pattern = r'(      # Load infrastructure information from deployment files\n      - name: 🔍 Load Infrastructure Information\n        id: load_info\n        run: \|\n          echo "🔍 Loading infrastructure information\.\.\."\n          \n          # Check if deployment info files exist\n          if \[ -f "\.deployment-info/infrastructure\.json" \]; then\n            echo "✅ Found deployment information file"\n            \n            # Extract information from JSON file\n            VM_NAME=\$\(jq -r \'\.vm_name // ""\' \.deployment-info/infrastructure\.json\)\n            VM_ZONE=\$\(jq -r \'\.vm_zone // ""\' \.deployment-info/infrastructure\.json\)\n            PROJECT_ID=\$\(jq -r \'\.project_id // ""\' \.deployment-info/infrastructure\.json\)\n            \n            # Override environment variables if found in deployment files\n            if \[ -n "\$VM_NAME" \]; then\n              echo "VM_INSTANCE=\$VM_NAME" >> \$GITHUB_ENV\n            fi\n            if \[ -n "\$VM_ZONE" \]; then\n              echo "VM_ZONE=\$VM_ZONE" >> \$GITHUB_ENV\n            fi\n            if \[ -n "\$PROJECT_ID" \]; then\n              echo "GCP_PROJECT_ID=\$PROJECT_ID" >> \$GITHUB_ENV\n            fi\n            \n            echo "📋 Using deployment files for infrastructure info"\n            echo "DEPLOYMENT_FILES_EXIST=true" >> \$GITHUB_ENV\n          else\n            echo "📋 Using GitHub Variables/inputs for infrastructure info"\n            echo "DEPLOYMENT_FILES_EXIST=false" >> \$GITHUB_ENV\n            \n            # Use original environment variables\n            echo "VM_INSTANCE=\$\{\{ env\.VM_INSTANCE \}\}" >> \$GITHUB_ENV\n            echo "VM_ZONE=\$\{\{ env\.VM_ZONE \}\}" >> \$GITHUB_ENV\n            echo "GCP_PROJECT_ID=\$\{\{ env\.GCP_PROJECT_ID \}\}" >> \$GITHUB_ENV\n          fi\n          \n          echo "📊 Infrastructure Information:"\n          echo "- VM Instance: \$\{VM_INSTANCE:-Not set\}"\n          echo "- VM Zone: \$\{VM_ZONE:-Not set\}"\n          echo "- Project ID: \$\{GCP_PROJECT_ID:-Not set\}")'
    
    replacement = '''      # Load infrastructure information from GitHub Variables only
      - name: 🔍 Load Infrastructure Information
        id: load_info
        run: |
          echo "🔍 Loading infrastructure information from GitHub Variables..."
          
          # Use GitHub Variables directly (no file dependencies)
          echo "VM_INSTANCE=${{ env.VM_INSTANCE }}" >> $GITHUB_ENV
          echo "VM_ZONE=${{ env.VM_ZONE }}" >> $GITHUB_ENV
          echo "GCP_PROJECT_ID=${{ env.GCP_PROJECT_ID }}" >> $GITHUB_ENV
          
          echo "📊 Infrastructure Information:"
          echo "- VM Instance: ${{ env.VM_INSTANCE }}"
          echo "- VM Zone: ${{ env.VM_ZONE }}"
          echo "- Project ID: ${{ env.GCP_PROJECT_ID }}"
          echo "📡 Using GitHub Variables for all infrastructure info (no files needed)"'''
    
    # Replace all occurrences
    new_content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
    
    if new_content != content:
        with open(filename, 'w') as f:
            f.write(new_content)
        print(f"✅ Updated {filename}")
        return True
    else:
        print(f"ℹ️ No changes needed in {filename}")
        return False

# Process workflow files
files_changed = []
for filename in [
    '.github/workflows/04-configure-environment.yml'
]:
    if optimize_workflow_file(filename):
        files_changed.append(filename)

if files_changed:
    print(f"\n✅ Optimized {len(files_changed)} files:")
    for f in files_changed:
        print(f"  - {f}")
else:
    print("\nℹ️ No files needed optimization")
