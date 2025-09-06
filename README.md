# Smart Gallery Filter WordPress Plugin (Elementor + Pods)
WordPress plugin that adds ##### 2. `pods-reset.sh` - Complete Data Removal
```bash
./scripts/pods-reset.sh
```dget for Elementor that enabl- 🚀 **Faster** than web interface
- 📊 **Detailed report** of what would be removed
- 🛡️ **Safe mode** for analysis first
- 🔄 **Automation** via scripts
- 📝 **Complete log** of process
- 🎯 **Automatic detection** of Pods CPTs
- 🖼️ **Removes featured images** automatically
- 🧹 **Advanced cleanup** of orphans and metadata

> **⚠️ WARNING:** This operation is **IRREVERSIBLE**!
> 
> Always execute first in analysis mode to check what would be affected.ion of a filterable gallery by taxonomies, CPTs and custom fields from Pods Framework. Easy, free and flexible for WordPress.

## 📋 Development Environment Prerequisites

- [Docker](https://www.docker.com/) installed
- [DDEV](https://ddev.com/) installed
- Git

## ⚡ Environment Setup

### 🚀 Quick Start (Automatizado)

**Para inicialização completa automatizada:**

```sh
git clone https://github.com/your-username/smart-gallery-filter.git
cd smart-gallery-filter
./init.sh
```

O script `init.sh` executa automaticamente:
- ✅ **Limpeza completa** (nuke do ambiente existente)
- ✅ **Inicialização DDEV** (containers Docker)
- ✅ **Setup WordPress** (instalação + plugins)
- ✅ **Reset Pods** (limpeza dos dados Pods)
- ✅ **Importação de dados** (196 carros + 5 concessionárias)

⚠️ **Atenção:** Todos os dados existentes serão perdidos durante a inicialização.

### 🔧 Setup Manual (Passo a Passo)

1. Clone the repository:
	```sh
	git clone https://github.com/your-username/smart-gallery-filter.git
	cd smart-gallery-filter
	```

2. Start DDEV environment:
	```sh
	ddev start
	```

3. Execute WordPress setup script:
	```sh
	./scripts/wp-setup.sh
	```
	Este script irá:
	- Instalar WordPress com dados padrão
	- Instalar e ativar plugins necessários (Elementor, Pods)
	- Ativar smart-gallery-filter plugin
	- Configurar HTTPS com mkcert

4. Import demo data (optional):
	```sh
	./scripts/pods-import.sh
	```

5. Access the site:
	- [https://smart-gallery-filter.ddev.site](https://smart-gallery-filter.ddev.site)

## 🔑 Default Access Data
- User: `admin`
- Password: `admin`
- Email: `admin@local.test`

## 📝 Notes
- The plugin will be automatically activated after WordPress installation.
- To install other plugins or themes, use the commands:
  ```sh
  ddev wp plugin install <plugin-name>
  ddev wp theme install <theme-name>
  ```

## 📄 Documentação oficial
- [DDEV Docs](https://ddev.readthedocs.io/en/stable/)
- [WordPress CLI](https://developer.wordpress.org/cli/commands/)

## � Scripts de Automação

### 🚀 `init.sh` - Inicialização Completa

**Script master que automatiza todo o processo de configuração:**

```bash
./init.sh
```

**O que faz:**
1. 💥 **Limpeza completa** (`nuke.sh`) - Remove ambiente existente
2. 🐳 **Inicialização DDEV** - Starta containers Docker
3. 🔧 **Setup WordPress** (`wp-setup.sh`) - Instala WP + plugins
4. 🧹 **Reset Pods** (`pods-reset.sh`) - Limpa dados Pods existentes
5. 📦 **Importação dados** (`pods-import.sh`) - Carrega 196 carros

**Características:**
- ✅ **Processo totalmente automatizado** (zero interação manual)
- ✅ **Verificação de erros** em cada etapa
- ✅ **Output colorido** com progresso visual
- ✅ **Confirmação de segurança** antes da execução
- ⚠️ **Destrutivo** - Remove todos os dados existentes

**Ideal para:**
- 🆕 **Primeira instalação**
- 🔄 **Reset completo do ambiente**
- 🏃‍♂️ **Setup rápido para novos desenvolvedores**

---

## �🚗 Test Data Mass for Development

The project includes scripts to generate test data mass with a complete car catalog to test gallery and filter functionalities.

### 📦 Available Scripts

**Location:** `demo-data/`

#### 🚀 Interactive Scripts (Recommended)

##### 1. `pods-import.sh` - Car Catalog Import
```bash
./scripts/pods-import.sh
```
- ✅ Interactive script with user confirmation
- ✅ Automatic DDEV status check
- ✅ Creates 'car' CPT with taxonomies (brand, type, fuel, transmission)
- ✅ Imports **196 cars** with realistic data
- ✅ Automatic upload of **196 featured images**
- ✅ Associates taxonomies based on filenames
- ✅ Provides admin URL after completion
- 🖼️ Uses real images from `demo-data/images/` (included in repository)

##### 2. `pods-reset.sh` - Complete Pods Reset
```bash
./demo-data/pods-reset.sh
```
- ✅ Interactive script with **double confirmation**
- ✅ Shows analysis first (what will be removed)
- ✅ Requires typing "DELETE" to confirm
- ✅ 3-second countdown with abort option
- ✅ Automatic DDEV status check
- ✅ No need to manually edit PHP files
- ⚠️ **IRREVERSIBLE** operation with safety prompts

#### 🛠️ Direct Scripts (Advanced Users)

##### 1. `pods-import.php` - Direct Car Catalog Import
```bash
ddev exec wp eval-file scripts/pods-import.php
```
- ✅ Creates 'car' CPT with taxonomies (brand, type, fuel, transmission)
- ✅ Imports **196 cars** with realistic data
- ✅ Automatic upload of **196 featured images**
- ✅ Associates taxonomies based on filenames
- 🖼️ Uses real images from `demo-data/images/` (included in repository)

##### 2. `pods-reset.php` - Direct Complete Pods Reset

This script replicates **exactly** the functionality **"Pods Admin > Settings > Cleanup & Reset > Reset Pods entirely"** via WP-CLI.

##### 📋 Usage

**Analysis (Safe Mode):**
```bash
ddev exec wp eval-file demo-data/pods-reset.php
```
- ✅ Dry run mode only (always safe)
- ✅ Shows everything that would be removed
- ✅ No manual configuration needed
- 🔍 Use for analysis before using interactive script

**Real Execution:**
```bash
# Use interactive script (RECOMMENDED)
./demo-data/pods-reset.sh
```
- ✅ Safe interactive script with confirmations
- ✅ No manual file editing required
- ✅ Analysis first, then execution with double confirmation

##### ⚠️ What will be removed

**CUSTOM DATA:**
- ✅ All posts from CPTs created by Pods
- ✅ All terms from custom taxonomies
- ✅ **Featured images** from posts (via `_thumbnail_id`)
- ✅ **Direct attachments** (via `post_parent`)
- ✅ Metadata related to CPTs

**PODS STRUCTURES:**
- ✅ CPT definitions (`_pods_pod`)
- ✅ Field group definitions (`_pods_group`) 
- ✅ Field definitions (`_pods_field`)

**CONFIGURATIONS:**
- ✅ All Pods options in database
- ✅ Cache and transients
- ✅ Custom tables
- ✅ Pods widgets

**ADVANCED CLEANUP:**
- ✅ **Orphan posts** that might have remained
- ✅ **Orphan metadata** (without associated post)
- ✅ **Related orphan attachments**

##### ⚡ Advantages over Admin

- 🚀 **Mais rápido** que a interface web
- 📊 **Relatório detalhado** do que será removido
- 🛡️ **Modo seguro** para análise primeiro
- 🔄 **Automação** via scripts
- 📝 **Log completo** do processo
- 🎯 **Detecção automática** de CPTs do Pods
- 🖼️ **Remove featured images** automaticamente
- 🧹 **Limpeza avançada** de órfãos e metadados

> **⚠️ ATENÇÃO:** Esta operação é **IRREVERSÍVEL**!
> 
> Sempre execute primeiro no modo análise para verificar o que será afetado.

### 🎯 Recommended Development Flow

1. **Import test data (Interactive):**
   ```bash
   ./demo-data/pods-import.sh
   ```

2. **Develop and test** plugin functionalities

3. **Clean environment when necessary (Interactive):**
   ```bash
   ./demo-data/pods-reset.sh
   ```

4. **Repeat** the process as needed

#### 🔧 Alternative Flow (Direct Commands)

1. **Import test data (Direct):**
   ```bash
   ddev exec wp eval-file scripts/pods-import.php
   ```

2. **Dry run before reset (Direct):**
   ```bash
   ddev exec wp eval-file scripts/pods-reset.php
   ```

3. **Reset when necessary:**
   ```bash
   ./scripts/pods-reset.sh
   ```

---

## 📋 Resumo de Scripts Disponíveis

### 🏠 Raiz do Projeto
- **`init.sh`** - Inicialização completa automatizada (nuke + setup + dados)

### 📁 `scripts/` - Scripts de Automação
- **`wp-setup.sh`** - Setup WordPress com plugins
- **`pods-import.sh`** - Importação de dados demo (196 carros)
- **`pods-reset.sh`** - Reset completo dos dados Pods
- **`nuke.sh`** - Destruição completa do ambiente
- **`backup.sh`** - Backup do WordPress
- **`restore.sh`** - Restauração de backups

### ⚡ Comandos DDEV Disponíveis
- **`ddev phpmyadmin`** - Acesso ao phpMyAdmin
- **`ddev start`** - Iniciar ambiente
- **`ddev stop`** - Parar ambiente
- **`ddev ssh`** - Acesso SSH ao container

---

## 🎯 Desenvolvimento

Este projeto usa uma estrutura modular com scripts automatizados para facilitar o desenvolvimento e testes do widget Elementor Smart Gallery Filter.

**Happy coding! 🚀**
