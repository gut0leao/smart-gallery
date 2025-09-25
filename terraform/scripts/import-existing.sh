#!/bin/bash

# Script to import existing GCP resources into Terraform state
# This prevents "resource already exists" errors

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Check required variables
if [ -z "$TF_VAR_project_id" ] || [ -z "$TF_VAR_environment" ] || [ -z "$TF_VAR_region" ]; then
    error "Required environment variables not set:"
    echo "  TF_VAR_project_id"
    echo "  TF_VAR_environment" 
    echo "  TF_VAR_region"
    exit 1
fi

PROJECT_ID="$TF_VAR_project_id"
ENVIRONMENT="$TF_VAR_environment"
REGION="$TF_VAR_region"
ZONE="${REGION}-a"
NAME_PREFIX="smart-gallery-${ENVIRONMENT}"

log "Starting import of existing resources..."
log "Project: $PROJECT_ID"
log "Environment: $ENVIRONMENT"
log "Region: $REGION"

# Function to safely import a resource
safe_import() {
    local tf_resource="$1"
    local gcp_resource_id="$2"
    local gcp_check_command="$3"
    local resource_description="$4"
    
    log "Checking $resource_description..."
    
    # Check if already in Terraform state
    if terraform state show "$tf_resource" >/dev/null 2>&1; then
        success "$resource_description already managed by Terraform"
        return 0
    fi
    
    # Check if exists in GCP
    if eval "$gcp_check_command" >/dev/null 2>&1; then
        log "Found existing $resource_description in GCP, importing..."
        if terraform import "$tf_resource" "$gcp_resource_id"; then
            success "Successfully imported $resource_description"
        else
            warn "Failed to import $resource_description - may need manual intervention"
            return 1
        fi
    else
        log "$resource_description does not exist in GCP - will be created"
    fi
}

# Import network resources
safe_import \
    "google_compute_network.smart_gallery_network" \
    "$NAME_PREFIX-network" \
    "gcloud compute networks describe '$NAME_PREFIX-network' --project='$PROJECT_ID'" \
    "VPC Network"

safe_import \
    "google_compute_subnetwork.smart_gallery_subnet" \
    "$REGION/$NAME_PREFIX-subnet" \
    "gcloud compute networks subnets describe '$NAME_PREFIX-subnet' --region='$REGION' --project='$PROJECT_ID'" \
    "Subnet"

safe_import \
    "google_compute_address.smart_gallery_ip" \
    "$REGION/$NAME_PREFIX-ip" \
    "gcloud compute addresses describe '$NAME_PREFIX-ip' --region='$REGION' --project='$PROJECT_ID'" \
    "Static IP Address"

# Import firewall rules
for fw_rule in "ssh" "http" "https" "icmp"; do
    tf_resource_name=$(echo "$fw_rule" | tr '-' '_')
    safe_import \
        "google_compute_firewall.smart_gallery_$tf_resource_name" \
        "$PROJECT_ID/$NAME_PREFIX-allow-$fw_rule" \
        "gcloud compute firewall-rules describe '$NAME_PREFIX-allow-$fw_rule' --project='$PROJECT_ID'" \
        "Firewall rule: $fw_rule"
done

# Import VM instance
safe_import \
    "google_compute_instance.smart_gallery_vm" \
    "$PROJECT_ID/$ZONE/$NAME_PREFIX-vm" \
    "gcloud compute instances describe '$NAME_PREFIX-vm' --zone='$ZONE' --project='$PROJECT_ID'" \
    "VM Instance"

success "Import process completed!"
log "You can now run 'terraform plan' to see the current state"