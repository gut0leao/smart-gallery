#!/bin/bash

# Smart Gallery Filter - Complete Environment Destruction
# Este script remove completamente o ambiente DDEV e Docker

echo "💥 Smart Gallery Filter - Environment Destroyer"
echo "==============================================="

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${RED}⚠️  DESTRUIÇÃO COMPLETA DO AMBIENTE${NC}"
echo ""
echo -e "${YELLOW}Este script vai:${NC}"
echo "🗑️ Parar e remover todos os containers"
echo "🗑️ Remover todas as imagens Docker"
echo "🗑️ Limpar volumes e networks"
echo "🗑️ Remover projeto DDEV"
echo "🗑️ Limpar cache Docker"
echo ""
echo -e "${RED}⚠️  ESTA AÇÃO É IRREVERSÍVEL!${NC}"
echo -e "${RED}⚠️  TODO SEU TRABALHO SERÁ PERDIDO!${NC}"
echo ""

read -p "Tem CERTEZA ABSOLUTA que quer destruir tudo? (digite 'DESTROY'): " confirm
if [ "$confirm" != "DESTROY" ]; then
    echo -e "${GREEN}😌 Cancelado. Ambiente preservado.${NC}"
    exit 0
fi

echo ""
echo -e "${RED}💀 INICIANDO DESTRUIÇÃO...${NC}"
echo ""

# Step 1: Stop DDEV project
echo -e "${BLUE}1/7${NC} 🛑 Parando projeto DDEV..."
if ddev stop 2>/dev/null; then
    echo "   ✅ Projeto parado"
else
    echo "   ⚠️ Projeto já estava parado ou erro"
fi

# Step 2: Delete DDEV project
echo -e "${BLUE}2/7${NC} 🗑️ Removendo projeto DDEV..."
if ddev delete --yes 2>/dev/null; then
    echo "   ✅ Projeto removido"
else
    echo "   ⚠️ Projeto já removido ou erro"
fi

# Step 3: Remove all containers (including stopped ones)
echo -e "${BLUE}3/7${NC} 📦 Removendo containers..."
containers=$(docker ps -aq 2>/dev/null)
if [ ! -z "$containers" ]; then
    docker rm -f $containers 2>/dev/null
    echo "   ✅ Containers removidos"
else
    echo "   ℹ️ Nenhum container encontrado"
fi

# Step 4: Remove all images
echo -e "${BLUE}4/7${NC} 🖼️ Removendo imagens Docker..."
images=$(docker images -aq 2>/dev/null)
if [ ! -z "$images" ]; then
    docker rmi -f $images 2>/dev/null
    echo "   ✅ Imagens removidas"
else
    echo "   ℹ️ Nenhuma imagem encontrada"
fi

# Step 5: Remove all volumes
echo -e "${BLUE}5/7${NC} 💾 Removendo volumes..."
volumes=$(docker volume ls -q 2>/dev/null)
if [ ! -z "$volumes" ]; then
    docker volume rm $volumes 2>/dev/null
    echo "   ✅ Volumes removidos"
else
    echo "   ℹ️ Nenhum volume encontrado"
fi

# Step 6: Remove all networks
echo -e "${BLUE}6/7${NC} 🌐 Removendo networks..."
networks=$(docker network ls --filter type=custom -q 2>/dev/null)
if [ ! -z "$networks" ]; then
    docker network rm $networks 2>/dev/null
    echo "   ✅ Networks removidas"
else
    echo "   ℹ️ Nenhuma network customizada encontrada"
fi

# Step 7: System cleanup
echo -e "${BLUE}7/7${NC} 🧹 Limpeza final do sistema..."
docker system prune -af --volumes 2>/dev/null
echo "   ✅ Sistema limpo"

echo ""
echo -e "${GREEN}💀 DESTRUIÇÃO COMPLETA!${NC}"
echo ""
echo -e "${YELLOW}Para recriar o ambiente:${NC}"
echo "1. ddev start"
echo "2. ./wp-setup.sh"
echo "3. ./demo-data/pods-import.sh"
echo ""
echo -e "${GREEN}🎉 Ambiente completamente destruído e limpo!${NC}"
