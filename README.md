# Smart Gallery

A smart WordPress plugin that creates filterable galleries using Elementor widgets with Pods Framework integration. Features filters based Pods Framework metadata (includind Custom Post Types with custom fields and hierarchical taxonomies).

![WordPress](https://img.shields.io/badge/WordPress-6.7+-blue?logo=wordpress)
![Elementor](https://img.shields.io/badge/Elementor-3.31+-purple?logo=elementor)
![Pods](https://img.shields.io/badge/Pods-3.3+-green)
![DDEV](https://img.shields.io/badge/DDEV-Docker-blue?logo=docker)

## 🚀 Features

### 🎨 **Core Gallery System**
- **Elementor Widget Integration** - Native widget for page builders
- **Pods Framework Support** - Custom post types and fields
- **Responsive Grid Layout** - Adaptive columns (desktop/tablet/mobile)
- **Interactive Hover Effects** - Image zoom and content reveal animations

### 🔍 **Advanced Search & Filtering** 
- **Text Search** - Server-side search with manual submission
- **Custom Fields Filtering** - Dynamic field-based filtering with count display
- **Taxonomy Filtering** - Hierarchical taxonomy support (including shared taxonomies)
- **Combined Filtering** - Search + custom fields + taxonomies work together
- **Smart Filter Management** - Clear individual filters or all at once
- **URL Persistence** - All filter states preserved in URLs for bookmarking

### 🗂️ **Content Management**
- **Hierarchical Taxonomies** - Multi-level location filtering (Country → State → City)
- **Dynamic Content Loading** - Only show relevant filter options with counts
- **Shared Taxonomy Support** - Handle taxonomies used by multiple CPTs
- **Pagination Integration** - Seamless pagination with search and filtering
- **Real-time Status Display** - Debug panel for development and testing

### 🎯 **Demo Environment**
- **Comprehensive Demo Data** - 196 cars + 5 dealerships with real images
- **Automated Development Environment** - One-command setup with DDEV
- **Complete Test Dataset** - 52 brands, 22 body types, hierarchical locations

## 📋 Prerequisites

- [Docker](https://www.docker.com/) installed
- [DDEV](https://ddev.com/) installed  
- [Git](https://git-scm.com/) installed
- **zip** utility installed (for packaging scripts)
- [mkcert](https://github.com/FiloSottile/mkcert) (optional, for HTTPS)

## ⚡ Quick Start

### 🎯 Complete Automated Setup

**For complete environment initialization:**

```bash
git clone https://github.com/gut0leao/smart-gallery.git
cd smart-gallery
./init.sh
```

The `init.sh` script automatically executes:
1. 💥 **Complete cleanup** (nuke existing environment)
2. 🐳 **DDEV initialization** (Docker containers)
3. 🔧 **WordPress setup** (installation + plugins)
4. 🧹 **Pods data reset** (clean Pods data)
5. 📦 **Demo data import** (196 cars + 5 dealerships)

⚠️ **Warning:** All existing data will be lost during initialization.

### 🔧 Manual Setup (Step by Step)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/gut0leao/smart-gallery.git
   cd smart-gallery
   ```

2. **Start DDEV environment:**
   ```bash
   ddev start
   ```

3. **Execute WordPress setup:**
   ```bash
   ./scripts/wp-setup.sh
   ```
   This script will:
   - Install WordPress with default data
   - Install and activate required plugins (Elementor, Pods)
   - Activate smart-gallery plugin
   - Configure HTTPS with mkcert

4. **Import demo data (optional):**
   ```bash
   ./scripts/pods-import.sh
   ```

## 🌐 Access Information

### Default Credentials
- **Site:** [https://smart-gallery.ddev.site](https://smart-gallery.ddev.site)
- **Admin:** [https://smart-gallery.ddev.site/wp-admin](https://smart-gallery.ddev.site/wp-admin)
- **Username:** `admin`
- **Password:** `admin`
- **Email:** `admin@example.com`

### Additional Access
- **phpMyAdmin:** `ddev phpmyadmin`
- **Mailhog:** `ddev launch -m`
- **DDEV Info:** `ddev describe`

## 🛠️ Automation Scripts

### 🏠 Root Directory

#### `init.sh` - Complete Initialization
**Master script that automates the entire setup process:**

```bash
./init.sh
```

**What it does:**
1. 💥 **Complete cleanup** (`nuke.sh`) - Removes existing environment
2. 🐳 **DDEV initialization** - Starts Docker containers  
3. 🔧 **WordPress setup** (`wp-setup.sh`) - Installs WP + plugins
4. 🧹 **Pods reset** (`pods-reset.sh`) - Cleans existing Pods data
5. 📦 **Data import** (`pods-import.sh`) - Loads 196 cars + 5 dealerships

**Features:**
- ✅ **Fully automated process** (zero manual interaction)
- ✅ **Error checking** at each step
- ✅ **Colored output** with visual progress
- ✅ **Safety confirmation** before execution
- ⚠️ **Destructive** - Removes all existing data

**Ideal for:**
- 🆕 **First installation**
- 🔄 **Complete environment reset**  
- 🏃‍♂️ **Quick setup for new developers**

### 📁 `scripts/` Directory

#### `wp-setup.sh` - WordPress Configuration
```bash
./scripts/wp-setup.sh
```
- ✅ Downloads and installs WordPress
- ✅ Creates wp-config.php with DDEV settings
- ✅ Installs and activates Elementor + Pods
- ✅ Activates Smart Gallery plugin
- ✅ Configures HTTPS with mkcert (SSL certificates)
- ✅ Provides complete setup summary

#### `pods-import.sh` - Demo Data Import  
```bash
./scripts/pods-import.sh
```
- ✅ Interactive script with environment validation
- ✅ Creates 'car' and 'dealer' custom post types
- ✅ Imports **196 cars** with realistic data
- ✅ Imports **5 specialized dealerships**
- ✅ Uploads **196 featured images** automatically
- ✅ Creates **hierarchical location taxonomy** (58 terms)
- ✅ Associates cars with dealers by location
- 🖼️ Uses real images from `demo-data/images/`

#### `pods-reset.sh` - Complete Data Reset
```bash
./scripts/pods-reset.sh
```
- ✅ Interactive script with **double confirmation**
- ✅ Shows analysis first (what will be removed)
- ✅ Complete removal of Pods data and structures
- ✅ Removes featured images automatically
- ✅ Advanced cleanup of orphans and metadata
- ⚠️ **IRREVERSIBLE** operation with safety prompts

#### `nuke.sh` - Environment Destroyer
```bash
./scripts/nuke.sh
```
- 💥 Stops and removes DDEV project
- 🗑️ Removes project-specific containers and images
- 🧹 Cleans volumes and networks
- 🛡️ Conservative cleanup (preserves Docker base images)
- ⚠️ **Complete environment destruction**

#### `backup.sh` - WordPress Backup
```bash
./scripts/backup.sh
```
- 💾 Creates complete WordPress backup
- 📦 Includes database and files
- 🏷️ Timestamped backup files

#### `restore.sh` - WordPress Restore
```bash
./scripts/restore.sh
```
- 🔄 Restores WordPress from backup
- 📋 Lists available backups
- ✅ Complete environment restoration

#### `map_backup_dir.sh` - Backup Directory Mapping
```bash
./scripts/map_backup_dir.sh /path/to/backup/directory
```
- 🔗 Maps local directory to project backups
- ☁️ Recommended for cloud-synchronized directories
- 📁 Creates symbolic link for backup storage

## 🚗 Demo Data Details

### Cars Database
- **196 vehicles** with real specifications
- **52 car brands** (Audi, BMW, Ferrari, etc.)
- **22 body types** (Sedan, SUV, Coupe, etc.)
- **4 fuel types** (Gasoline, Diesel, Hybrid, Electric)
- **3 transmission types** (Manual, Automatic, CVT)

### Dealerships
- **5 specialized dealerships** across different locations
- **Premium Motors** (New York) - Luxury brands (Audi, BMW)
- **City Auto Center** (California) - Reliable brands (Honda, Nissan)  
- **Sports Car Depot** (Florida) - Performance cars (Ferrari, Lamborghini)
- **Family Auto Sales** (Texas) - Family vehicles (Chevrolet, Ford)
- **Electric Future Motors** (Washington) - Electric vehicles (Tesla, Leaf)

### Hierarchical Locations
- **58 location terms** organized hierarchically
- **3 countries:** United States, Canada, United Kingdom
- **13 states/provinces** with major cities
- **42 cities** distributed across regions

## 🎯 Development Workflow

### Recommended Development Flow

1. **Complete environment setup:**
   ```bash
   ./init.sh
   ```

2. **Develop and test** widget functionalities

3. **Reset environment when needed:**
   ```bash
   ./scripts/pods-reset.sh
   ./scripts/pods-import.sh
   ```

4. **Create backups** of working configurations:
   ```bash
   ./scripts/backup.sh
   ```

### Available DDEV Commands

- `ddev start` - Start environment
- `ddev stop` - Stop environment  
- `ddev restart` - Restart environment
- `ddev ssh` - SSH access to container
- `ddev logs` - View container logs
- `ddev phpmyadmin` - Access phpMyAdmin
- `ddev describe` - Show project information

## 📦 Project Structure

```
smart-gallery/
├── wp-content/plugins/smart-gallery/  # Main plugin directory
│   ├── smart-gallery.php              # Plugin main file
│   └── ...                                   # Plugin files
├── scripts/                                  # Automation scripts
│   ├── wp-setup.sh                           # WordPress setup
│   ├── pods-import.sh                        # Demo data import
│   ├── pods-reset.sh                         # Data reset
│   ├── nuke.sh                               # Environment cleanup
│   ├── backup.sh                             # Backup creation
│   ├── restore.sh                            # Backup restoration
│   └── map_backup_dir.sh                     # Backup directory mapping
├── demo-data/                                # Demo data and images
│   ├── images/                               # 196 car images
│   └── README.md                             # Demo data documentation
├── ssl-certs/                                # SSL certificates (git ignored)
│   ├── README.md                             # SSL documentation
│   └── *.pem                                 # Certificate files
├── init.sh                                   # Master initialization script
└── README.md                                 # This file
```

## 🔒 Security Notes

- SSL certificates are stored in `ssl-certs/` directory
- Certificate files (*.pem, *.key) are automatically ignored by Git  
- Only development certificates - not for production use
- Admin credentials are default - change for production environments

## 🎨 Plugin Development

The Smart Gallery plugin is designed with a modern, modular architecture:

### 🏗️ **Architecture Overview**
- **Elementor Widget Integration** - Custom widget with comprehensive controls
- **Pods Framework Integration** - Complete CPT and taxonomy support  
- **Modular Design** - Separate classes for rendering, controls, and data integration
- **Clean Code Standards** - PSR-4 autoloading, proper namespacing, and documentation

### ✨ **Current Features (Implemented)**
- ✅ **F1.1-F1.4**: Complete gallery foundation (display, Pods integration, controls, hover effects)
- ✅ **F2.1**: Pagination system with search/filter integration
- ✅ **F3.1**: Text search with manual submission and URL persistence
- ✅ **F3.2**: Custom fields filtering with dynamic loading and count display
- ✅ **F3.3**: Taxonomy filtering with shared taxonomy support (e.g., car_brand for Cars + Dealers)
- ✅ **F4.4**: Debug status panel for development transparency

### 🔧 **Technical Features**
- **SVG Icon System** - Professional UI with inline SVG icons and screen reader support
- **Responsive CSS** - Mobile-first design with Elementor theme integration
- **Dynamic Filtering** - Real-time filter option updates based on current result set
- **URL State Management** - Complete filter state preservation in URLs
- **Performance Optimized** - Efficient database queries and minimal resource usage

### 🎯 **Development Focus**
- Provide flexible filtering options for gallery content
- Support hierarchical taxonomies for complex categorization
- Enable easy customization and extension
- Maintain high performance with large datasets
- Follow WordPress and Elementor best practices

## 🚀 Local-Only Development

This repository now focuses exclusively on local development using DDEV. All CI/CD, Terraform, and cloud deployment automation have been removed. For working locally:

- Use `./init.sh` for a full local setup
- Use scripts under `scripts/` for WordPress setup, demo data, reset, backup and restore
- No cloud or remote automation is included in this project anymore

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **WordPress** - Content management platform
- **Elementor** - Page builder integration  
- **Pods Framework** - Custom content management
- **DDEV** - Development environment
- **mkcert** - Local SSL certificates

---

**Happy coding! 🚀**
