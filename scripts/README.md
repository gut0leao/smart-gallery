# 📜 Scripts Directory

Este diretório contém scripts para diferentes aspectos do desenvolvimento e deploy do Smart Gallery Plugin.

## 📁 Estrutura dos Scripts

```
scripts/
├── wp-setup.sh              # ⚙️  Configuração inicial do WordPress no DDEV  
├── pods-import.sh            # 📊 Importação de dados demo do Pods
├── pods-reset.sh             # 🗑️  Reset dos dados do Pods
├── package.sh                # 📦 Criação de pacote ZIP do plugin
├── deploy-package-local.sh   # 🏠 Deploy de pacote para ambiente local DDEV
├── deploy-package-github.sh  # 🐙 Deploy de pacote para GitHub Packages
├── backup.sh                 # 💾 Backup do banco de dados
├── restore.sh                # ♻️  Restauração do banco de dados
└── README.md                 # 📖 Esta documentação
```

## 📦 Estrutura de Saída

```
dist/                                 # 🚫 Todo diretório ignorado pelo git
├── builds/                           # 📦 Pacotes finais
│   ├── smart-gallery-1.0.0.zip      # Plugin empacotado
│   ├── smart-gallery-1.0.0.zip.sha256
│   ├── smart-gallery-1.0.0.zip.md5
│   └── smart-gallery-1.0.0.info     # Informações de deployment
└── smart-gallery/                    # 🔧 Arquivos temporários de build
    ├── assets/
    ├── includes/
    └── smart-gallery.php
```

> 📝 **Nota**: Todo o diretório `dist/` está no `.gitignore` - nenhum arquivo de build será commitado.tory

Este diretório contém scripts para diferentes aspectos do desenvolvimento e deploy do Smart Gallery Plugin.

## � Estrutura dos Scripts

```
scripts/
├── wp-setup.sh           # ⚙️  Configuração inicial do WordPress no DDEV  
├── pods-import.sh         # 📊 Importação de dados demo do Pods
├── pods-reset.sh          # 🗑️  Reset dos dados do Pods
├── package.sh             # 📦 Criação de pacote ZIP do plugin
├── deploy-local.sh        # 🏠 Deploy para ambiente local DDEV
├── deploy-github.sh       # 🐙 Deploy para GitHub Packages
├── backup.sh              # 💾 Backup do banco de dados
├── restore.sh             # ♻️  Restauração do banco de dados
└── README.md              # 📖 Esta documentação
```

## 🎯 Propósito de Cada Script

### 🏗️ **Desenvolvimento Local**

#### `wp-setup.sh` - Configuração WordPress
```bash
./scripts/wp-setup.sh
```
- **Função**: Configura WordPress do zero no DDEV
- **Uso**: Primeira configuração ou reinstalação completa
- **Inclui**: WordPress core, plugins essenciais, configurações básicas

#### `pods-import.sh` - Importação Dados Demo
```bash  
./scripts/pods-import.sh
```
- **Função**: Importa dados demo (carros, dealers, taxonomias)
- **Uso**: Após configurar WordPress, para ter dados de teste
- **Dados**: 196+ carros, 5 dealers, taxonomias completas

#### `deploy-package-local.sh` - Deploy Local de Pacote
```bash
./scripts/deploy-package-local.sh
```
- **Função**: Atualiza o plugin no WordPress local DDEV
- **Uso**: Durante desenvolvimento, para testar mudanças
- **Ações**: Validação, ativação, limpeza de cache

### 📦 **Empacotamento e Distribuição**

#### `package.sh` - Criação de Pacote
```bash
./scripts/package.sh
```
- **Função**: Cria ZIP limpo do plugin para distribuição
- **Uso**: Preparar plugin para upload/deploy
- **Saída**: Arquivos organizados em `dist/builds/` (ZIP, checksums, info)
- **Validação**: Sintaxe PHP, segurança, estrutura

#### `deploy-package-github.sh` - Deploy GitHub Packages
```bash
./scripts/deploy-package-github.sh [version]
```
- **Função**: Publica pacote do plugin no GitHub Container Registry
- **Uso**: Distribuição via Docker/GitHub Packages
- **Recursos**: Versionamento, releases automáticos, metadados

### 🗄️ **Backup e Manutenção**

#### `backup.sh` - Backup Database
```bash
./scripts/backup.sh
```
- **Função**: Cria backup do banco de dados
- **Uso**: Antes de mudanças importantes
- **Saída**: Arquivo SQL com timestamp

#### `restore.sh` - Restauração Database
```bash
./scripts/restore.sh [backup-file]
```
- **Função**: Restaura backup do banco de dados  
- **Uso**: Reverter para estado anterior
- **Entrada**: Arquivo de backup SQL

## 🔄 Fluxos de Trabalho Típicos

### 🆕 **Configuração Inicial**
```bash
# 1. Configurar WordPress
./scripts/wp-setup.sh

# 2. Importar dados demo
./scripts/pods-import.sh

# 3. Testar funcionalidades
# Acessar https://smart-gallery.ddev.site
```

### 💻 **Desenvolvimento Diário**
```bash
# Após fazer mudanças no código:
./scripts/deploy-package-local.sh

# Para testar com dados limpos:
./scripts/pods-reset.sh
./scripts/pods-import.sh
```

### 📤 **Preparação para Release**
```bash
# 1. Criar backup (segurança)
./scripts/backup.sh

# 2. Testar localmente
./scripts/deploy-package-local.sh

# 3. Criar pacote para distribuição
./scripts/package.sh
# ✅ Arquivos criados em: dist/builds/smart-gallery-x.x.x.zip

# 4. Deploy para GitHub Packages (opcional)
./scripts/deploy-package-github.sh v1.0.0
```

### 🚨 **Recuperação de Emergência**
```bash
# Restaurar backup recente
./scripts/restore.sh

# Ou reinstalar do zero
./scripts/wp-setup.sh
./scripts/pods-import.sh
```

## ⚙️ Requisitos dos Scripts

### 🌍 **Ambiente Global**
- **DDEV**: Todos os scripts assumem ambiente DDEV
- **Bash**: Shell compatível com Bash 4.0+
- **Git**: Para informações de versão (opcional)
- **zip**: Utilitário de compactação (instalado automaticamente pelo `../init.sh`)

### 📦 **Scripts Específicos**

| Script | Requisitos Adicionais |
|--------|----------------------|
| `package.sh` | zip¹, find, sha256sum |
| `deploy-package-github.sh` | GitHub CLI, Docker |
| `backup.sh` | mysqldump (via DDEV) |
| `restore.sh` | mysql (via DDEV) |

¹ _zip é instalado automaticamente pelo script `../init.sh`_

## 🔧 Configuração dos Scripts

### � **GitHub Packages Setup**
```bash
# Instalar GitHub CLI
# https://cli.github.com/

# Autenticar
gh auth login

# Configurar Docker para GitHub Packages
# (feito automaticamente pelo script)
```

### 🎛️ **Variáveis de Ambiente**
```bash
# Para deploy-package-github.sh
export CLEANUP_IMAGES=true    # Limpar imagens Docker após push
export GITHUB_TOKEN=ghp_...   # Token GitHub (opcional, usa gh auth)

# Para package.sh  
export FORCE_LOCAL=1          # Forçar PHP local (pular DDEV)
```

## 🐛 Troubleshooting

### ❌ **Problemas Comuns**

#### DDEV não encontrado
```bash
# Instalar DDEV primeiro
# https://ddev.readthedocs.io/en/latest/users/install/

# Verificar instalação
ddev version
```

#### Zip não encontrado
```bash
# O zip é instalado automaticamente pelo init.sh
# Mas se precisar instalar manualmente:

# Ubuntu/Debian
sudo apt-get install zip

# macOS
brew install zip

# CentOS/RHEL
sudo yum install zip
```

#### WordPress não instalado
```bash
# Executar configuração
./scripts/wp-setup.sh

# Verificar status
ddev exec wp core is-installed
```

#### Permissões de arquivo
```bash
# Tornar scripts executáveis
chmod +x scripts/*.sh

# Verificar estrutura de arquivos
ls -la scripts/
```

#### Falha na criação de pacote
```bash
# Verificar sintaxe PHP manualmente
find wp-content/plugins/smart-gallery -name "*.php" -exec ddev exec php -l {} \;

# Verificar estrutura do plugin
ls -la wp-content/plugins/smart-gallery/
```

### 🔍 **Debug dos Scripts**

```bash
# Executar com verbose (se suportado)
bash -x ./scripts/script-name.sh

# Verificar logs DDEV
ddev logs

# Testar comandos individuais
ddev exec wp plugin list
ddev exec wp core is-installed
```

---

## 📚 Documentação Adicional

- **CI/CD Pipeline**: [../.github/README.md](../.github/README.md)
- **Deployment Guide**: [../.github/DEPLOYMENT.md](../.github/DEPLOYMENT.md)
- **Plugin Requirements**: [../docs/requirements.md](../docs/requirements.md)
- **Main README**: [../README.md](../README.md)
