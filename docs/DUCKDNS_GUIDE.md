# Duck DNS Integration Guide

## 🦆 What is Duck DNS?

Duck DNS is a free dynamic DNS service that provides free subdomains under `duckdns.org`. It's perfect for development, testing, and personal projects because:

- ✅ **Completely Free** - No cost, no limits
- ✅ **Easy Setup** - Simple API for updates
- ✅ **Fast Propagation** - Usually 1-5 minutes
- ✅ **No Registration** - Just email and password
- ✅ **Automation Friendly** - Perfect for CI/CD

## 🚀 Quick Setup

### 1. Create Duck DNS Account
1. Go to [duckdns.org](https://www.duckdns.org/)
2. Sign in with Google, GitHub, Twitter, or Reddit
3. Create a subdomain (e.g., `mysmartgallery`)
4. Note your token (needed for automation)

### 2. Add Token to GitHub Secrets
1. Go to your GitHub repository
2. Settings → Secrets and variables → Actions
3. Click "New repository secret"
4. Name: `DUCKDNS_TOKEN`
5. Value: Your Duck DNS token
6. Click "Add secret"

### 3. Use in Workflow
When running the "Provision Infrastructure" workflow:
1. ✅ Check "Use Duck DNS for automatic DNS management"
2. Enter your subdomain in "Duck DNS subdomain" (without .duckdns.org)
3. Set "Domain name" to your full domain (e.g., `mysmartgallery.duckdns.org`)

## 📋 Example Configuration

```yaml
# Workflow inputs example:
domain_name: mysmartgallery.duckdns.org
use_duckdns: true
duckdns_subdomain: mysmartgallery
```

## 🔧 Manual Duck DNS Update

If you need to update manually:

```bash
# Update Duck DNS record
curl "https://www.duckdns.org/update?domains=SUBDOMAIN&token=TOKEN&ip=IP_ADDRESS"
```

## 🌐 Benefits for Smart Gallery

- **Zero DNS Configuration** - Automatic setup
- **Instant Testing** - Start development immediately
- **SSL Compatible** - Works with Let's Encrypt
- **WordPress Ready** - Perfect for WordPress sites
- **GitHub Actions Integration** - Seamless automation

## 🔍 Verification

After provisioning, verify your DNS:

```bash
# Check DNS resolution
nslookup mysmartgallery.duckdns.org

# Check from different DNS servers
nslookup mysmartgallery.duckdns.org 8.8.8.8
nslookup mysmartgallery.duckdns.org 1.1.1.1
```

## 🆚 Duck DNS vs Custom Domain

| Feature | Duck DNS | Custom Domain |
|---------|----------|---------------|
| Cost | Free | Varies ($10-15/year) |
| Setup | Automatic | Manual DNS config |
| Propagation | 1-5 minutes | 5-24 hours |
| Professional | No | Yes |
| Custom branding | No | Yes |
| SSL Support | Yes | Yes |

## 💡 Best Practices

1. **Use descriptive subdomains**: `smartgallery-staging`, `myproject-dev`
2. **Keep tokens secure**: Never commit tokens to code
3. **Use different subdomains**: For staging vs production
4. **Test DNS first**: Before running configure workflow
5. **Monitor expiration**: Duck DNS domains expire if unused for 30 days

## 🔧 Troubleshooting

### DNS Not Resolving
- Wait 5-10 minutes for propagation
- Check token and subdomain name
- Verify IP address is correct
- Try different DNS servers

### SSL Issues
- Ensure DNS is resolving first
- Wait for full propagation before SSL setup
- Check domain name matches exactly

### Token Issues
- Get fresh token from duckdns.org
- Ensure no extra spaces in secret
- Check token has sufficient permissions

## 🎯 Integration with Smart Gallery Workflows

The Duck DNS integration works seamlessly with all Smart Gallery workflows:

1. **Provision Infrastructure** → Auto-configures Duck DNS
2. **Install Packages** → Uses stored domain info
3. **Configure Environment** → Sets up SSL for Duck DNS domain
4. **Deploy Plugin** → Uses configured domain

This creates a completely automated deployment pipeline from infrastructure to live WordPress site!