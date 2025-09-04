# Smart Gallery Filter WordPress Plugin (Elementor + Pods)
WordPress plugin that adds a widget for Elementor that enabl- 🚀 **Faster** than web interface
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
	ddev exec setup-wordpress
	```
	This script will:
	- Download WordPress files
	- Install WordPress with default data
	- Activate smart-gallery-filter plugin

4. Access the site:
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

## 🚗 Test Data Mass for Development

The project includes scripts to generate test data mass with a complete car catalog to test gallery and filter functionalities.

### 📦 Available Scripts

**Location:** `demo-data/`

#### 🚀 Interactive Scripts (Recommended)

##### 1. `pods-import.sh` - Car Catalog Import
```bash
./demo-data/pods-import.sh
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
ddev exec wp eval-file demo-data/pods-import.php
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
   ddev exec wp eval-file demo-data/pods-import.php
   ```

2. **Dry run before reset (Direct):**
   ```bash
   ddev exec wp eval-file demo-data/pods-reset.php
   ```

3. **Reset when necessary:**
   ```bash
   ./demo-data/pods-reset.sh
   ```
