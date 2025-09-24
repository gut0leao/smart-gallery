# 🔐 GitHub Actions Setup Guide

Este documento explica como configurar os secrets necessários para a pipeline de CI/CD do Smart Gallery Plugin.

## 📋 Secrets Necessários

Configure os seguintes secrets no GitHub (Settings > Secrets and variables > Actions):

### 🌐 Google Cloud Platform (GCP)

```bash
# Service Account Key (JSON format)
GCP_SA_KEY='{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "smart-gallery-deploy@your-project.iam.gserviceaccount.com",
  "client_id": "...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token"
}'

# GCP Project ID
GCP_PROJECT_ID=your-gcp-project-id

# VM Instance Details
GCP_VM_INSTANCE=wordpress-vm-instance
GCP_VM_ZONE=us-central1-a

# Site URL for health checks
SITE_URL=https://your-demo-site.com
```

## 🛠️ GCP Setup Instructions

### 1. Create Service Account

```bash
# Create service account
gcloud iam service-accounts create smart-gallery-deploy \
    --description="Service account for Smart Gallery plugin deployment" \
    --display-name="Smart Gallery Deploy"

# Get the service account email
SA_EMAIL=$(gcloud iam service-accounts list \
    --filter="displayName:Smart Gallery Deploy" \
    --format="value(email)")

echo "Service Account Email: $SA_EMAIL"
```

### 2. Grant Required Permissions

```bash
# Grant Compute Instance Admin role
gcloud projects add-iam-policy-binding $GCP_PROJECT_ID \
    --member="serviceAccount:$SA_EMAIL" \
    --role="roles/compute.instanceAdmin.v1"

# Grant Compute OS Login role
gcloud projects add-iam-policy-binding $GCP_PROJECT_ID \
    --member="serviceAccount:$SA_EMAIL" \
    --role="roles/compute.osLogin"

# Grant Service Account User role
gcloud projects add-iam-policy-binding $GCP_PROJECT_ID \
    --member="serviceAccount:$SA_EMAIL" \
    --role="roles/iam.serviceAccountUser"
```

### 3. Create Service Account Key

```bash
# Create and download key
gcloud iam service-accounts keys create ~/smart-gallery-sa-key.json \
    --iam-account=$SA_EMAIL

# Display key content (copy this to GCP_SA_KEY secret)
cat ~/smart-gallery-sa-key.json

# Remove local key file for security
rm ~/smart-gallery-sa-key.json
```

## 🖥️ VM Setup Requirements

### WordPress VM Prerequisites

Sua VM do GCP deve ter:

```bash
# 1. WordPress instalado e funcionando
# 2. WP-CLI instalado
curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# 3. Configurar sudo sem senha para www-data (ou ajustar scripts)
echo "www-data ALL=(ALL) NOPASSWD: /usr/local/bin/wp" | sudo tee /etc/sudoers.d/wp-cli

# 4. Configurar SSH keys para GitHub Actions
# (O service account criado acima já terá acesso SSH via OS Login)
```

### WordPress Configuration

```php
// wp-config.php - Certificar que estas configurações estão presentes:

// Allow plugin/theme installation
define('FS_METHOD', 'direct');

// Increase memory limit if needed
define('WP_MEMORY_LIMIT', '256M');

// Enable WordPress debugging (opcional para staging)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🔄 Pipeline Flow

### 1. **Build Job**
- ✅ PHP syntax check
- ✅ Code quality analysis
- ✅ Security checks
- ✅ Version management
- ✅ Plugin packaging (ZIP)
- ✅ Checksum generation

### 2. **Deploy Job**
- ✅ Download build artifacts
- ✅ Verify package integrity
- ✅ Upload to GCP VM
- ✅ Backup existing plugin
- ✅ Install new version
- ✅ Activate plugin
- ✅ Clear caches
- ✅ Health check

### 3. **Release Job** (Tags only)
- ✅ Create GitHub Release
- ✅ Upload plugin ZIP
- ✅ Generate release notes

## 🚀 Usage

### Automatic Deployment
```bash
# Deploy to staging on main branch push
git push origin main

# Deploy to production with version tag
git tag v1.0.0
git push origin v1.0.0
```

### Manual Deployment
```bash
# Via GitHub Actions UI
# Go to Actions tab > Smart Gallery Plugin - Build & Deploy > Run workflow
# Select environment: staging or production
```

## 🛡️ Security Best Practices

1. **Service Account**: Use principle of least privilege
2. **Secrets**: Never commit secrets to repository
3. **VM Access**: Use OS Login instead of SSH keys when possible
4. **HTTPS**: Always use HTTPS for site URLs
5. **Backups**: Pipeline creates automatic backups before deployment

## 🔍 Troubleshooting

### Common Issues

```bash
# 1. SSH Connection Failed
# Check if OS Login is enabled and service account has required roles

# 2. Plugin Activation Failed
# Check WordPress permissions and WP-CLI configuration

# 3. Health Check Failed
# Verify SITE_URL is correct and site is accessible

# 4. Permission Denied
# Check file ownership: chown -R www-data:www-data /var/www/html/wp-content/plugins/
```

### Debug Commands

```bash
# Test SSH connection manually
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE --command="echo 'SSH OK'"

# Test WP-CLI on VM
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE --command="cd /var/www/html && sudo -u www-data wp --info"

# Check plugin status
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE --command="cd /var/www/html && sudo -u www-data wp plugin list"
```

## 📊 Monitoring

A pipeline inclui:
- ✅ Build status notifications
- ✅ Deployment success/failure alerts
- ✅ Health checks pós-deployment
- ✅ Artifact retention (30 dias)
- ✅ Release automation para tags

---

**Nota**: Ajuste os valores dos secrets conforme sua configuração específica do GCP e WordPress.