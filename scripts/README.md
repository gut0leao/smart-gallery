# Smart Gallery Filter - Scripts

Este diretório contém todos os scripts de automação para o projeto Smart Gallery Filter.

## 📁 Estrutura do Diretório

```
scripts/
├── README.md           # Esta documentação
├── wp-setup.sh         # Setup inicial do WordPress
├── nuke.sh            # Destruição completa do ambiente
├── pods-import.sh     # Importação de dados demo
├── pods-import.php    # Script PHP de importação (usado internamente)
├── pods-reset.sh      # Reset dos dados demo  
├── pods-reset.php     # Script PHP para reset (usado internamente)
├── backup.sh          # Backup do site WordPress
├── restore.sh         # Restauração de backups
└── map_backup_dir.sh  # Mapeamento de diretório de backups
```

## 🚀 Scripts Principais

### Setup e Configuração

**`./scripts/wp-setup.sh`**
- Configura WordPress do zero com plugins necessários
- Instala Elementor, Pods Framework e ativa Smart Gallery Filter
- Configura HTTPS com mkcert
- Solicita confirmação antes de sobrescrever instalação existente

### Dados Demo

**`./scripts/pods-import.sh`**
- Importa dados demo completos (196 carros + 5 dealers)
- Cria taxonomias hierárquicas (País → Estado → Cidade)
- Associa imagens como WordPress native featured images
- Configura relacionamentos entre carros e dealers

**`./scripts/pods-reset.sh`**
- Remove completamente todos os dados demo
- Limpa taxonomias, termos e configurações Pods
- Preserva WordPress core e outros plugins

### Gerenciamento de Ambiente

**`./scripts/nuke.sh`**
- **⚠️ PERIGOSO:** Destroi completamente o ambiente DDEV
- Remove containers, imagens, volumes Docker
- Requer confirmação explícita ('DESTROY')

### Backup e Restore

**`./scripts/backup.sh`**
- Cria backup completo (banco + uploads)
- Salva em diretório sincronizado (configurável)
- Compressão automática com timestamp

**`./scripts/restore.sh`**
- Restaura backups anteriores
- Lista backups disponíveis interativamente
- Restaura banco de dados e arquivos

**`./scripts/map_backup_dir.sh`**
- Mapeia diretório local para backups
- Recomendado usar diretório sincronizado (OneDrive, etc.)
- Cria symlink na raiz do projeto

### Comandos DDEV Disponíveis

Para acessar o **phpMyAdmin**, use o comando DDEV nativo:
```bash
ddev phpmyadmin
```

## 🔧 Requisitos

- **WordPress com ambiente DDEV**
- **Pods Framework plugin ativo**
- **Smart Gallery Filter plugin ativo**
- **Espaço em disco suficiente** para 196 imagens de carros
- **mkcert** (opcional, para HTTPS automático)

## 📋 Ordem Recomendada de Execução

1. **Primeira instalação:**
   ```bash
   ./scripts/wp-setup.sh
   ./scripts/pods-import.sh
   ```

2. **Para reset durante desenvolvimento:**
   ```bash
   ./scripts/pods-reset.sh
   ./scripts/pods-import.sh
   ```

3. **Para backup periódico:**
   ```bash
   ./scripts/map_backup_dir.sh /caminho/para/backup/sincronizado
   ./scripts/backup.sh
   ```

4. **Para destruição completa (cuidado!):**
   ```bash
   ./scripts/nuke.sh
   ```

## 🎯 Funcionalidades dos Dados Demo

### Carros (196 veículos)
- Preços realistas ($15.000 - $150.000)
- Anos (1990-2024)
- Quilometragem (5.000 - 80.000 miles)  
- Especificações de motor e recursos
- Associados com dealers por especialização de marca
- WordPress native featured images

### Dealers (5 concessionárias)
- **Premium Motors** (New York) - Marcas de luxo (BMW, Mercedes-Benz, Audi)
- **City Auto Center** (California) - Marcas populares (Toyota, Honda, Nissan)
- **Sports Car Depot** (Florida) - Carros esportivos (Ferrari, Porsche, Lamborghini)
- **Family Auto Sales** (Texas) - Veículos familiares (Ford, Chevrolet, Hyundai)
- **Electric Future Motors** (Washington) - Eco-friendly (Tesla, Prius, Leaf)

### Taxonomias
- **Car Brand** (~52 termos, compartilhados com dealers)
- **Car Body Type** (~22 termos)
- **Car Fuel Type** (4 termos: gasoline, diesel, hybrid, electric)
- **Car Transmission** (3 termos: automatic, manual, cvt)
- **Car Location** (58 termos, **hierárquicos**):
  - 3 Países: United States, Canada, United Kingdom
  - 11 Estados/Regiões: California, New York, Texas, Florida, Washington, Ontario, Quebec, British Columbia, England, Scotland, Wales
  - 44 Cidades: Todas aninhadas adequadamente sob seus respectivos estados/regiões
- **Dealer Location** (5 termos)

## ⚠️ Avisos Importantes

- **`./scripts/nuke.sh`** é **IRREVERSÍVEL** e destrói todo o ambiente
- Scripts de backup requerem configuração de diretório via `map_backup_dir.sh`
- Dados demo podem ocupar espaço significativo (~200MB de imagens)
- Execute sempre a partir da raiz do projeto para caminhos corretos
