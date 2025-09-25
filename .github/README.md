# 🚀 CI/CD Pipeline - Smart Gallery Plugin

Este diretório contém a configuração completa de CI/CD para o Smart Gallery Plugin usando GitHub Actions e Google Cloud Platform.

## 📁 Estrutura

```
.github/
├── workflows/
│   └── deploy.yml              # Pipeline principal de CI/CD
├── DEPLOYMENT.md               # Guia de configuração detalhado
└── README.md                   # Este arquivo
```

## 🔄 Pipeline Overview

### 🏗️ **Build Job**
- **PHP Syntax Check**: Valida todos os arquivos `.php`
- **Code Quality**: Verifica segurança, escaping, estrutura
- **Version Management**: Determina versão baseada em tags/commits
- **Package Creation**: Cria ZIP do plugin com checksums
- **Artifact Upload**: Disponibiliza package para deploy

### 🚀 **Deploy Job**
- **Environment**: Staging (main) / Production (tags)
- **GCP Integration**: Upload e execução via gcloud
- **Plugin Management**: Backup, instalação, ativação
- **Health Check**: Verifica se o site está funcionando
- **Cache Management**: Limpa caches WordPress

### 📦 **Release Job** (Tags apenas)
- **GitHub Release**: Cria release automático
- **Asset Upload**: Plugin ZIP e checksums
- **Release Notes**: Documentação automática

## 🎯 Workflows Disponíveis

### 🔧 Modulares (Recomendado)
| Workflow | Propósito | Inputs Principais |
|----------|-----------|-------------------|
| 🧹 Cleanup GCP | Limpeza de recursos | project_id, dry_run |
| 🏗️ Provision Infrastructure | Criar VM + rede | project_id, domain_name |
| 📦 Install Packages | LAMP stack | vm_instance, php_version |
| ⚙️ Configure Environment | WordPress + SSL | domain_name, admin_email |
| 🚀 Deploy Plugin | Smart Gallery | vm_instance, plugin_version |

### 📈 Single Deploy (Legado)
| Trigger | Environment | Action |
|---------|-------------|--------|
| `push main` | Staging | Build + Deploy |
| `push tag v*` | Production | Build + Deploy + Release |
| Manual dispatch | Staging/Production | Build + Deploy |

## 🔧 Setup Rápido

### 1. Configure Repository Secrets
```bash
# Settings > Secrets and variables > Actions > Secrets
GCP_SA_KEY          # Service Account JSON (OBRIGATÓRIO)

# Settings > Secrets and variables > Actions > Variables (OPCIONAL)
GCP_PROJECT_ID      # ID do projeto GCP
GCP_VM_INSTANCE     # Nome da VM
GCP_VM_ZONE         # Zona da VM
```

### 2. Crie Environment (Opcional)
```bash
# Settings > Environments > New environment
# Nome: "production" (para workflow cleanup)
```

### 3. Execute Workflows Modulares
```bash
# Para novo projeto (sequência completa):
1. 🧹 Cleanup GCP Resources (se necessário)
2. 🏗️ Provision Infrastructure
3. 📦 Install Packages  
4. ⚙️ Configure Environment
5. 🚀 Deploy Plugin

# Apenas deploy do plugin:
- 🚀 Deploy Plugin
```

## 🛠️ Scripts Locais

### Deploy Local (DDEV)
```bash
# Para ambiente local DDEV
./scripts/deploy-package-local.sh

# Automaticamente:
# - Cria package
# - Deploy no DDEV WordPress
# - Ativa plugin
```

### Deploy GitHub Packages
```bash
# Para publicar no GitHub Container Registry
./scripts/deploy-package-github.sh v1.0.0

# Cria:
# - Docker image
# - GitHub Release
# - Container package
```

### Testes Manuais
```bash
# PHP syntax check
find wp-content/plugins/smart-gallery -name "*.php" -exec php -l {} \;

# Security scan
grep -r "eval(" wp-content/plugins/smart-gallery --include="*.php"

# Package test
unzip -t smart-gallery-*.zip
```

## 🔍 Troubleshooting

### Common Issues

#### ❌ Build Failed
```bash
# Check PHP syntax
php -l wp-content/plugins/smart-gallery/file.php

# Check file structure
ls -la wp-content/plugins/smart-gallery/
```

#### ❌ Deploy Failed
```bash
# Test GCP connection
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE

# Check WP-CLI
sudo -u www-data wp --info

# Verify permissions
ls -la /var/www/html/wp-content/plugins/
```

#### ❌ Health Check Failed
```bash
# Test site response
curl -I https://your-site.com

# Check WordPress status
sudo -u www-data wp core is-installed
```

### Debug Commands

```bash
# View pipeline logs
# GitHub Actions > Workflow run > Job details

# Manual deploy test
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE --command="
  cd /var/www/html && 
  sudo -u www-data wp plugin list &&
  sudo -u www-data wp plugin status smart-gallery
"

# Check plugin files
gcloud compute ssh VM_INSTANCE --zone=VM_ZONE --command="
  ls -la /var/www/html/wp-content/plugins/smart-gallery/
"
```

## 📊 Monitoring & Notifications

### Build Status
- ✅ Success: Plugin deployed successfully
- ❌ Failure: Check logs for specific error
- ⚠️ Warning: Built but deployment issues

### Deployment Verification
- Site health check (HTTP 200)
- Plugin activation status
- WordPress functionality test

### Artifacts
- **Retention**: 30 days
- **Contents**: Plugin ZIP, checksums, metadata
- **Location**: GitHub Actions artifacts

## 🔐 Security

### Best Practices
- Service Account com mínimos privilégios necessários
- Secrets nunca commitados no código
- HTTPS obrigatório para todas as conexões
- Backups automáticos antes de cada deploy
- Verificação de integridade com checksums

### Access Control
- GCP Service Account: Compute Admin + OS Login
- GitHub Secrets: Repository level, encrypted
- VM Access: OS Login, sem SSH keys permanentes

## 📈 Performance

### Pipeline Timing
- **Build**: ~2-3 minutos
- **Deploy**: ~3-5 minutos  
- **Total**: ~5-8 minutos

### Optimizations
- PHP extensions caching
- Parallel job execution where possible
- Minimal artifact size
- Efficient file transfer

---

## 🆘 Support

Para problemas com a pipeline:

1. **Check Logs**: GitHub Actions > Workflow run
2. **Verify Secrets**: Ensure all required secrets are set
3. **Test Local**: Use `./scripts/deploy.sh`
4. **GCP Status**: Check VM and service account status
5. **WordPress Health**: Verify WordPress installation

**Documentação Completa**: [DEPLOYMENT.md](DEPLOYMENT.md)