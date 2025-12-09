# 📜 Scripts (Local DDEV)

Este diretório contém apenas scripts para desenvolvimento local com DDEV. Toda automação de CI/CD, empacotamento e deploy em nuvem foi removida.

## 📁 Scripts Disponíveis

```
scripts/
├── wp-setup.sh        # Configuração inicial do WordPress no DDEV
├── pods-import.sh     # Importação de dados demo do Pods
├── pods-reset.sh      # Reset dos dados do Pods
├── backup.sh          # Backup do banco de dados
├── restore.sh         # Restauração do banco de dados
├── map_backup_dir.sh  # Mapeia diretório de backups
├── nuke.sh            # Limpeza completa do ambiente DDEV
└── README.md          # Esta documentação
```

## 🎯 Como usar

### 1) Configuração inicial
```bash
./scripts/wp-setup.sh
```

### 2) Dados de demonstração
```bash
./scripts/pods-import.sh
```

### 3) Reset de dados
```bash
./scripts/pods-reset.sh
```

### 4) Backup e restauração
```bash
./scripts/backup.sh
./scripts/restore.sh
```

### 5) Limpeza completa do ambiente
```bash
./scripts/nuke.sh
```

## ⚙️ Requisitos
- DDEV instalado e configurado
- Bash 4.0+

## 🐛 Troubleshooting
- Verificar instalação do DDEV: `ddev version`
- Tornar scripts executáveis: `chmod +x scripts/*.sh`
- Verificar se o WordPress está instalado: `ddev exec wp core is-installed`
