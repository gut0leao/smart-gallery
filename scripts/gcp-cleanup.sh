#!/bin/bash

# GCP Resource Cleanup Script
# This script helps clean up ALL resources in a GCP project
# WARNING: This will DELETE resources and may incur costs or data loss
# Use with extreme caution and only on test/development projects

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DRY_RUN=${DRY_RUN:-true}
FORCE=${FORCE:-false}
PROJECT_ID=""
CURRENT_PROJECT=""

# Helper functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}⚠️  WARNING: $1${NC}"
}

error() {
    echo -e "${RED}❌ ERROR: $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Function to run commands with timeout and better error handling
run_with_timeout() {
    local timeout_duration="$1"
    local cmd="$2"
    local description="$3"
    
    log "Running: $description"
    if timeout "$timeout_duration" bash -c "$cmd" 2>/dev/null; then
        return 0
    else
        warn "Command timed out or failed: $description"
        return 1
    fi
}

confirm() {
    local message="$1"
    if [ "$FORCE" = "true" ]; then
        return 0
    fi
    
    echo -e "${YELLOW}$message${NC}"
    read -p "Continue? (y/N): " -r
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Operation cancelled."
        return 1
    fi
}

# Check if credentials file exists and setup authentication
check_credentials() {
    log "Checking GCP credentials..."
    
    # Check for credentials directory and files
    CREDENTIALS_DIR=".credentials"
    SERVICE_ACCOUNT_KEY=""
    
    if [ -d "$CREDENTIALS_DIR" ]; then
        # Look for JSON key files
        local json_files=$(find "$CREDENTIALS_DIR" -name "*.json" 2>/dev/null | head -1)
        if [ -n "$json_files" ]; then
            SERVICE_ACCOUNT_KEY="$json_files"
            log "Found credentials file: $SERVICE_ACCOUNT_KEY"
        fi
    fi
    
    if [ -z "$SERVICE_ACCOUNT_KEY" ] || [ ! -f "$SERVICE_ACCOUNT_KEY" ]; then
        error "No GCP credentials found in .credentials/ directory"
        echo ""
        echo "📋 Para criar e configurar credenciais do GCP:"
        echo ""
        echo "1️⃣  Acesse o Google Cloud Console:"
        echo "   https://console.cloud.google.com/"
        echo ""
        echo "2️⃣  Selecione ou crie um projeto"
        echo ""
        echo "3️⃣  Vá para 'IAM & Admin' → 'Service Accounts'"
        echo "   https://console.cloud.google.com/iam-admin/serviceaccounts"
        echo ""
        echo "4️⃣  Clique em 'Create Service Account'"
        echo "   • Nome: gcp-cleanup-service"
        echo "   • Descrição: Service account for GCP resource cleanup"
        echo ""
        echo "5️⃣  Conceda as seguintes permissões (Roles):"
        echo "   • Compute Admin (roles/compute.admin)"
        echo "   • Storage Admin (roles/storage.admin)" 
        echo "   • Cloud SQL Admin (roles/cloudsql.admin)"
        echo "   • Cloud Functions Admin (roles/cloudfunctions.admin)"
        echo "   • Cloud Run Admin (roles/run.admin)"
        echo "   • Kubernetes Engine Admin (roles/container.admin)"
        echo "   • Service Account Admin (roles/iam.serviceAccountAdmin)"
        echo "   • Project IAM Admin (roles/resourcemanager.projectIamAdmin)"
        echo ""
        echo "6️⃣  Criar e baixar chave JSON:"
        echo "   • Clique na service account criada"
        echo "   • Vá para aba 'Keys'"
        echo "   • 'Add Key' → 'Create new key' → 'JSON'"
        echo "   • Baixe o arquivo JSON"
        echo ""
        echo "7️⃣  Salvar credenciais no projeto:"
        echo "   mkdir -p .credentials"
        echo "   # Mova o arquivo JSON baixado para:"
        echo "   # .credentials/gcp-service-account.json"
        echo ""
        echo "8️⃣  Execute novamente este script"
        echo ""
        echo "⚠️  IMPORTANTE:"
        echo "   • Mantenha o arquivo JSON seguro e nunca faça commit no Git"
        echo "   • O diretório .credentials já está no .gitignore"
        echo "   • Use apenas em projetos de teste/desenvolvimento"
        echo ""
        exit 1
    fi
    
    # Authenticate using service account
    log "Authenticating with service account..."
    if ! gcloud auth activate-service-account --key-file="$SERVICE_ACCOUNT_KEY" &>/dev/null; then
        error "Failed to authenticate with service account key: $SERVICE_ACCOUNT_KEY"
        echo ""
        echo "Verifique se:"
        echo "• O arquivo JSON é válido"
        echo "• A service account tem as permissões necessárias"
        echo "• O arquivo não foi corrompido durante o download"
        exit 1
    fi
    
    success "Successfully authenticated with service account"
}

# Check if user is authenticated and has project set
check_auth() {
    log "Checking GCP authentication status..."
    
    # Get current authenticated account
    local active_account=$(gcloud auth list --filter=status:ACTIVE --format="value(account)" 2>/dev/null | head -n 1)
    if [ -z "$active_account" ]; then
        error "No active authentication found"
        exit 1
    fi
    
    log "Active account: $active_account"
    
    CURRENT_PROJECT=$(gcloud config get-value project 2>/dev/null || echo "")
    if [ -z "$CURRENT_PROJECT" ]; then
        warn "No default project set"
        log "Available projects for authenticated account:"
        gcloud projects list --format="table(projectId,name,lifecycleState)" 2>/dev/null || {
            error "Cannot list projects. Check if the service account has proper permissions."
            exit 1
        }
        echo ""
        read -p "Enter project ID to use: " CURRENT_PROJECT
        if [ -z "$CURRENT_PROJECT" ]; then
            error "Project ID is required"
            exit 1
        fi
        gcloud config set project "$CURRENT_PROJECT"
    fi
    
    success "Authenticated. Current project: $CURRENT_PROJECT"
}

# List all projects and let user select
select_project() {
    log "Available projects:"
    gcloud projects list --format="table(projectId,name,lifecycleState)"
    
    echo ""
    read -p "Enter project ID to clean (or press Enter to use current: $CURRENT_PROJECT): " PROJECT_ID
    
    if [ -z "$PROJECT_ID" ]; then
        PROJECT_ID="$CURRENT_PROJECT"
    fi
    
    # Verify project exists and user has access
    if ! gcloud projects describe "$PROJECT_ID" &>/dev/null; then
        error "Cannot access project '$PROJECT_ID'. Check project ID and permissions."
        exit 1
    fi
    
    # Set the project
    gcloud config set project "$PROJECT_ID"
    
    warn "Selected project: $PROJECT_ID"
    warn "This will potentially DELETE ALL RESOURCES in this project!"
    
    if ! confirm "Are you absolutely sure you want to proceed with cleanup?"; then
        exit 0
    fi
}

# Function to run command with dry-run support
run_cmd() {
    local cmd="$1"
    local description="$2"
    
    if [ "$DRY_RUN" = "true" ]; then
        log "[DRY RUN] Would execute: $description"
        log "[DRY RUN] Command: $cmd"
    else
        log "Executing: $description"
        eval "$cmd" || warn "Command failed (this might be expected): $cmd"
    fi
}

# Cleanup functions for different resource types

cleanup_compute() {
    log "🖥️  Cleaning up Compute Engine resources..."
    
    # Check if Compute Engine API is enabled
    local compute_enabled=$(gcloud services list --enabled --filter="config.name:compute.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$compute_enabled" ]; then
        log "Compute Engine API not enabled, skipping..."
        return 0
    fi
    
    # List instances
    log "Checking for VM instances..."
    local instances=$(timeout 30 gcloud compute instances list --format="value(name,zone)" 2>/dev/null || true)
    if [ -n "$instances" ]; then
        echo "$instances" | while read name zone; do
            if [ -n "$name" ] && [ -n "$zone" ]; then
                run_cmd "gcloud compute instances delete '$name' --zone='$zone' --quiet" "Delete VM instance: $name"
            fi
        done
    else
        log "No VM instances found"
    fi
    
    # List disks
    log "Checking for persistent disks..."
    local disks=$(timeout 30 gcloud compute disks list --format="value(name,zone)" 2>/dev/null || true)
    if [ -n "$disks" ]; then
        echo "$disks" | while read name zone; do
            if [ -n "$name" ] && [ -n "$zone" ]; then
                run_cmd "gcloud compute disks delete '$name' --zone='$zone' --quiet" "Delete disk: $name"
            fi
        done
    else
        log "No disks found"
    fi
    
    # List networks (keep default VPC unless forced)
    log "Checking for custom networks..."
    local networks=$(timeout 30 gcloud compute networks list --format="value(name)" --filter="name != default" 2>/dev/null || true)
    if [ -n "$networks" ]; then
        echo "$networks" | while read name; do
            if [ -n "$name" ]; then
                # Delete firewall rules first
                local fw_rules=$(timeout 30 gcloud compute firewall-rules list --format="value(name)" --filter="network:$name" 2>/dev/null || true)
                if [ -n "$fw_rules" ]; then
                    echo "$fw_rules" | while read fw_name; do
                        if [ -n "$fw_name" ]; then
                            run_cmd "gcloud compute firewall-rules delete '$fw_name' --quiet" "Delete firewall rule: $fw_name"
                        fi
                    done
                fi
                
                run_cmd "gcloud compute networks delete '$name' --quiet" "Delete network: $name"
            fi
        done
    else
        log "No custom networks found"
    fi
    
    # List static IPs
    log "Checking for static IP addresses..."
    local ips=$(timeout 30 gcloud compute addresses list --format="value(name,region)" 2>/dev/null || true)
    if [ -n "$ips" ]; then
        echo "$ips" | while read name region; do
            if [ -n "$name" ]; then
                if [ -n "$region" ]; then
                    run_cmd "gcloud compute addresses delete '$name' --region='$region' --quiet" "Delete regional IP: $name"
                else
                    run_cmd "gcloud compute addresses delete '$name' --global --quiet" "Delete global IP: $name"
                fi
            fi
        done
    else
        log "No static IPs found"
    fi
}

cleanup_storage() {
    log "🪣  Cleaning up Cloud Storage..."
    
    # Check if Storage API is enabled
    local storage_enabled=$(gcloud services list --enabled --filter="config.name:storage.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$storage_enabled" ]; then
        log "Cloud Storage API not enabled, skipping..."
        return 0
    fi
    
    log "Checking for Cloud Storage buckets..."
    local buckets=$(timeout 30 gsutil ls 2>/dev/null | sed 's|gs://||' | sed 's|/||' || true)
    if [ -n "$buckets" ]; then
        echo "$buckets" | while read bucket; do
            if [ -n "$bucket" ]; then
                run_cmd "gsutil -m rm -r 'gs://$bucket'" "Delete bucket: $bucket"
            fi
        done
    else
        log "No storage buckets found"
    fi
}

cleanup_sql() {
    log "🗄️  Cleaning up Cloud SQL..."
    
    # Check if SQL Admin API is enabled
    local sql_enabled=$(gcloud services list --enabled --filter="config.name:sqladmin.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$sql_enabled" ]; then
        log "Cloud SQL Admin API not enabled, skipping..."
        return 0
    fi
    
    log "Checking for Cloud SQL instances..."
    local instances=$(timeout 30 gcloud sql instances list --format="value(name)" 2>/dev/null || true)
    if [ -n "$instances" ]; then
        echo "$instances" | while read name; do
            if [ -n "$name" ]; then
                run_cmd "gcloud sql instances delete '$name' --quiet" "Delete SQL instance: $name"
            fi
        done
    else
        log "No Cloud SQL instances found"
    fi
}

cleanup_functions() {
    log "⚡ Cleaning up Cloud Functions..."
    
    # Check if Cloud Functions API is enabled
    local functions_enabled=$(gcloud services list --enabled --filter="config.name:cloudfunctions.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$functions_enabled" ]; then
        log "Cloud Functions API not enabled, skipping..."
        return 0
    fi
    
    log "Checking for Cloud Functions..."
    local functions=$(timeout 30 gcloud functions list --format="value(name)" 2>/dev/null || true)
    if [ -n "$functions" ]; then
        echo "$functions" | while read name; do
            if [ -n "$name" ]; then
                run_cmd "gcloud functions delete '$name' --quiet" "Delete function: $name"
            fi
        done
    else
        log "No Cloud Functions found"
    fi
}

cleanup_run() {
    log "🏃 Cleaning up Cloud Run..."
    
    # First check if Cloud Run API is enabled
    local run_enabled=$(gcloud services list --enabled --filter="config.name:run.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$run_enabled" ]; then
        log "Cloud Run API not enabled, skipping..."
        return 0
    fi
    
    # List Cloud Run services with timeout
    log "Checking for Cloud Run services..."
    local services=$(timeout 30 gcloud run services list --format="value(metadata.name)" --platform=managed 2>/dev/null || true)
    
    if [ -n "$services" ]; then
        # Get all regions where Cloud Run is available
        local regions=$(gcloud run regions list --format="value(locationId)" 2>/dev/null || echo "us-central1 us-east1 europe-west1")
        
        echo "$services" | while read name; do
            if [ -n "$name" ]; then
                # Try to find the service in available regions
                local found_region=""
                for region in $regions; do
                    if timeout 10 gcloud run services describe "$name" --region="$region" --format="value(metadata.name)" &>/dev/null; then
                        found_region="$region"
                        break
                    fi
                done
                
                if [ -n "$found_region" ]; then
                    run_cmd "gcloud run services delete '$name' --region='$found_region' --quiet" "Delete Cloud Run service: $name (region: $found_region)"
                else
                    warn "Could not find region for Cloud Run service: $name"
                fi
            fi
        done
    else
        log "No Cloud Run services found"
    fi
}

cleanup_gke() {
    log "☸️  Cleaning up GKE clusters..."
    
    # Check if Container API is enabled
    local container_enabled=$(gcloud services list --enabled --filter="config.name:container.googleapis.com" --format="value(config.name)" 2>/dev/null || true)
    if [ -z "$container_enabled" ]; then
        log "Kubernetes Engine API not enabled, skipping..."
        return 0
    fi
    
    log "Checking for GKE clusters..."
    local clusters=$(timeout 30 gcloud container clusters list --format="value(name,zone)" 2>/dev/null || true)
    if [ -n "$clusters" ]; then
        echo "$clusters" | while read name zone; do
            if [ -n "$name" ] && [ -n "$zone" ]; then
                run_cmd "gcloud container clusters delete '$name' --zone='$zone' --quiet" "Delete GKE cluster: $name"
            fi
        done
    else
        log "No GKE clusters found"
    fi
}

cleanup_apis() {
    log "🔌 Listing enabled APIs..."
    
    local apis=$(gcloud services list --enabled --format="value(config.name)" --filter="config.name !~ 'googleapis.com'" 2>/dev/null || true)
    if [ -n "$apis" ]; then
        log "Enabled APIs (not automatically disabled for safety):"
        echo "$apis" | while read api; do
            if [ -n "$api" ]; then
                log "  - $api"
                # Uncomment the line below if you want to disable all non-default APIs
                # run_cmd "gcloud services disable '$api' --force" "Disable API: $api"
            fi
        done
        warn "APIs not automatically disabled. Disable manually if needed with: gcloud services disable <api-name>"
    else
        log "No custom APIs found"
    fi
}

cleanup_iam() {
    log "👤 Cleaning up custom IAM resources..."
    
    # List service accounts (excluding default ones)
    local service_accounts=$(gcloud iam service-accounts list --format="value(email)" --filter="email !~ 'gserviceaccount.com$' OR email ~ '.*@$PROJECT_ID.iam.gserviceaccount.com'" 2>/dev/null | grep -v "compute@\|firebase-adminsdk@" || true)
    if [ -n "$service_accounts" ]; then
        echo "$service_accounts" | while read email; do
            if [ -n "$email" ]; then
                run_cmd "gcloud iam service-accounts delete '$email' --quiet" "Delete service account: $email"
            fi
        done
    else
        log "No custom service accounts found"
    fi
}

cleanup_monitoring() {
    log "📊 Cleaning up monitoring resources..."
    
    # Note: Most monitoring resources are automatically cleaned when other resources are deleted
    log "Monitoring dashboards and alerts are typically cleaned automatically"
}

# Main cleanup function
main_cleanup() {
    log "🧹 Starting comprehensive GCP cleanup for project: $PROJECT_ID"
    
    if [ "$DRY_RUN" = "true" ]; then
        warn "DRY RUN MODE: No resources will actually be deleted"
        warn "Set DRY_RUN=false to actually delete resources"
    fi
    
    # Order matters - some resources depend on others
    cleanup_gke
    cleanup_run  
    cleanup_functions
    cleanup_compute
    cleanup_sql
    cleanup_storage
    cleanup_iam
    cleanup_apis
    cleanup_monitoring
    
    success "Cleanup process completed!"
    
    if [ "$DRY_RUN" = "false" ]; then
        log "Resources have been deleted. Some cleanup may take a few minutes to complete."
        log "Check the GCP Console to verify all resources are removed."
    fi
}

# Usage information
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "GCP Resource Cleanup Script"
    echo ""
    echo "OPTIONS:"
    echo "  -d, --dry-run      Show what would be deleted without actually deleting (default: true)"
    echo "  -f, --force        Skip confirmation prompts"
    echo "  -r, --real-run     Actually delete resources (sets DRY_RUN=false)"
    echo "  -h, --help         Show this help message"
    echo ""
    echo "EXAMPLES:"
    echo "  $0                 # Dry run - show what would be deleted"
    echo "  $0 --real-run      # Actually delete resources"
    echo "  $0 --force --real-run  # Delete without prompts (DANGEROUS!)"
    echo ""
    echo "ENVIRONMENT VARIABLES:"
    echo "  DRY_RUN=false      Actually execute deletions"
    echo "  FORCE=true         Skip confirmation prompts"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -r|--real-run)
            DRY_RUN=false
            shift
            ;;
        -f|--force)
            FORCE=true
            shift
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        *)
            error "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# Main execution
main() {
    log "🚀 GCP Resource Cleanup Script Starting..."
    
    check_credentials
    check_auth
    select_project
    
    if [ "$DRY_RUN" = "false" ]; then
        warn "⚠️  REAL DELETION MODE ACTIVATED!"
        warn "This will PERMANENTLY DELETE resources in project: $PROJECT_ID"
        if ! confirm "Last chance - are you absolutely sure?"; then
            exit 0
        fi
    fi
    
    main_cleanup
}

# Check if script is being sourced or executed
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi