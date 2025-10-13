## 🚀 Smart Gallery Plugin v1.5.0

### 📦 What's Included
- Complete Smart Gallery WordPress plugin
- Elementor widget integration  
- Advanced search and filtering system
- Taxonomy and custom fields support
- SVG icon system with professional UI

### 🎯 Features
- ✅ Text search with manual submission
- ✅ Custom fields filtering with dynamic loading
- ✅ Taxonomy filtering with hierarchical support
- ✅ Responsive design with hover effects
- ✅ URL state persistence
- ✅ Debug status panel

### 📥 Installation Options

#### Option 1: Download ZIP
1. Download the `smart-gallery-1.5.0.zip` file below
2. Upload to WordPress: Plugins > Add New > Upload
3. Activate the plugin
4. Add Smart Gallery widget in Elementor

#### Option 2: GitHub Packages (Docker)
```bash
# Pull from GitHub Container Registry
docker pull ghcr.io/gut0leao/smart-gallery:v1.5.0

# Extract plugin files
docker create --name temp-container ghcr.io/gut0leao/smart-gallery:v1.5.0
docker cp temp-container:/plugin.zip ./smart-gallery.zip
docker rm temp-container
```

### 🔗 Links
- [Documentation](https://github.com/gut0leao/smart-gallery/blob/main/README.md)
- [Requirements](https://github.com/gut0leao/smart-gallery/blob/main/docs/requirements.md)
- [GitHub Packages](https://github.com/gut0leao/smart-gallery/pkgs/container/smart-gallery)
