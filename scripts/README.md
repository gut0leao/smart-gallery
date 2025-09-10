# Smart Gallery - Scripts Directory

Automation scripts for project management and development workflow.

## 🚀 GitHub Issues Management

### **check-prerequisites.sh**
Verification script that checks if your environment is ready for issue creation:
- ✅ GitHub CLI installation
- ✅ Authentication status  
- ✅ Repository access permissions
- ✅ Existing issues detection (prevents duplicates)

### **create-issues.sh**
Automated script that creates the complete GitHub project structure:
- **16 feature issues** with detailed descriptions
- **6 milestones** organized by development phases
- **19 labels** with colors and categories
- Proper dependencies and acceptance criteria

## 📋 Usage

```bash
# Navigate to scripts directory
cd scripts

# 1. Check if everything is ready
./check-prerequisites.sh

# 2. If all good, create all issues
./create-issues.sh
```

## 🔧 Prerequisites

- **GitHub CLI**: Install from https://github.com/cli/cli#installation
- **Authentication**: Run `gh auth login`
- **Repository Access**: Write permissions to gut0leao/smart-gallery

## 🎯 What Gets Created

### Issues (16 total)
- **Phase 1 - Foundation**: F1.3, F1.2, F1.1 (3 issues)
- **Phase 2 - Core Features**: F2.3, F2.1, F2.2 (3 issues)  
- **Phase 3 - Search**: F3.1, F5.1 (2 issues)
- **Phase 4 - Advanced Filtering**: F3.2, F3.3, F3.4 (3 issues)
- **Phase 5 - Polish**: F4.1, F4.2, F4.3, F5.2 (4 issues)
- **Phase 6 - Final**: F5.3 (1 issue)

### Labels (19 total)
- **Phases**: phase-1-foundation through phase-6-final
- **Complexity**: low-complexity, medium-complexity, high-complexity  
- **Areas**: pods-framework, elementor, gallery, pagination, search, filtering, ux, styling, admin, documentation

### Milestones (6 total)
- Phase 1 - Foundation
- Phase 2 - Core Features
- Phase 3 - Search & Basic Filtering
- Phase 4 - Advanced Filtering
- Phase 5 - Polish & Enhancement
- Phase 6 - Finalization

## ⚠️ Important Notes

- Script creates ALL issues at once
- Check for existing issues before running
- Each issue includes detailed acceptance criteria
- Dependencies are properly mapped
- Estimated development time included

## 🔗 Links

- **Issues**: https://github.com/gut0leao/smart-gallery/issues
- **Milestones**: https://github.com/gut0leao/smart-gallery/milestones
- **Project Board**: https://github.com/gut0leao/smart-gallery/projects

---

## 🛠️ Other Scripts

### **Development Scripts**
- `backup.sh` - Database and files backup
- `restore.sh` - Restore from backup
- `nuke.sh` - Clean development reset
- `wp-setup.sh` - WordPress setup automation

### **Pods Scripts**
- `pods-import.sh` - Import Pods configuration
- `pods-reset.sh` - Reset Pods data
- `pods-import.php` - PHP import helper

**Note**: Run scripts from their respective directories for proper path resolution.
