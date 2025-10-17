#!/bin/bash
# Script para diagnosticar problemas de SSH com a VM do GCP

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔍 Diagnóstico da VM Smart Gallery"
echo "=================================="
echo ""

# Verificar se gcloud está instalado
if ! command -v gcloud &> /dev/null; then
    echo -e "${RED}❌ gcloud CLI não está instalado${NC}"
    exit 1
fi

# Configurar variáveis (ajuste conforme necessário)
PROJECT_ID="${GCP_PROJECT_ID:-silver-asset-177115}"
VM_INSTANCE="${GCP_VM_INSTANCE:-smart-gallery-staging-vm}"
VM_ZONE="${GCP_VM_ZONE:-us-central1-a}"

echo "📊 Configuração:"
echo "  Project ID: $PROJECT_ID"
echo "  VM Instance: $VM_INSTANCE"
echo "  VM Zone: $VM_ZONE"
echo ""

# Verificar se a VM existe
echo "🔍 Verificando se a VM existe..."
if gcloud compute instances describe "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE" &>/dev/null; then
    echo -e "${GREEN}✅ VM encontrada${NC}"
else
    echo -e "${RED}❌ VM não encontrada${NC}"
    echo "💡 Execute o workflow '2. Provision Infrastructure' para criar a VM"
    exit 1
fi

# Verificar status da VM
echo ""
echo "🔍 Verificando status da VM..."
VM_STATUS=$(gcloud compute instances describe "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE" --format="value(status)")
echo "  Status: $VM_STATUS"

if [ "$VM_STATUS" != "RUNNING" ]; then
    echo -e "${YELLOW}⚠️  VM não está rodando (Status: $VM_STATUS)${NC}"
    echo "💡 Iniciando VM..."
    gcloud compute instances start "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE"
    echo -e "${GREEN}✅ VM iniciada${NC}"
    echo "⏳ Aguardando 30 segundos para a VM ficar pronta..."
    sleep 30
else
    echo -e "${GREEN}✅ VM está rodando${NC}"
fi

# Verificar IP externo
echo ""
echo "🔍 Verificando IP externo..."
EXTERNAL_IP=$(gcloud compute instances describe "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE" --format="value(networkInterfaces[0].accessConfigs[0].natIP)")
if [ -n "$EXTERNAL_IP" ]; then
    echo -e "${GREEN}✅ IP Externo: $EXTERNAL_IP${NC}"
else
    echo -e "${RED}❌ VM não tem IP externo${NC}"
fi

# Verificar regras de firewall
echo ""
echo "🔍 Verificando regras de firewall para SSH (porta 22)..."
FIREWALL_RULES=$(gcloud compute firewall-rules list --project="$PROJECT_ID" --filter="allowed[].ports:22 OR allowed[].IPProtocol:tcp" --format="table(name,sourceRanges.list():label=SRC_RANGES,allowed[].ports.list():label=PORTS)")
if [ -n "$FIREWALL_RULES" ]; then
    echo -e "${GREEN}✅ Regras de firewall encontradas:${NC}"
    echo "$FIREWALL_RULES"
else
    echo -e "${YELLOW}⚠️  Nenhuma regra de firewall para SSH encontrada${NC}"
    echo "💡 Pode ser necessário criar uma regra de firewall para SSH"
fi

# Verificar chaves SSH na VM
echo ""
echo "🔍 Verificando metadados SSH da VM..."
SSH_KEYS=$(gcloud compute instances describe "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE" --format="value(metadata.items.ssh-keys)")
if [ -n "$SSH_KEYS" ]; then
    echo -e "${GREEN}✅ Chaves SSH configuradas na VM${NC}"
    echo "  Quantidade de chaves: $(echo "$SSH_KEYS" | wc -l)"
else
    echo -e "${YELLOW}⚠️  Nenhuma chave SSH encontrada nos metadados da VM${NC}"
    echo "💡 Certifique-se de que o secret VM_SSH_PRIVATE_KEY está configurado no GitHub"
fi

# Tentar conexão SSH
echo ""
echo "🧪 Testando conexão SSH..."
if gcloud compute ssh "$VM_INSTANCE" --project="$PROJECT_ID" --zone="$VM_ZONE" --command="echo 'Conexão SSH bem-sucedida'" 2>/dev/null; then
    echo -e "${GREEN}✅ Conexão SSH funcionando!${NC}"
else
    echo -e "${RED}❌ Falha na conexão SSH${NC}"
    echo ""
    echo "🔧 Possíveis soluções:"
    echo "1. Verifique se o secret VM_SSH_PRIVATE_KEY está configurado corretamente no GitHub"
    echo "2. Execute novamente o workflow '2. Provision Infrastructure' para reconfigurar a VM"
    echo "3. Verifique se há regras de firewall bloqueando a porta 22"
    echo ""
    echo "Para diagnóstico detalhado, execute:"
    echo "  gcloud compute ssh $VM_INSTANCE --project=$PROJECT_ID --zone=$VM_ZONE --troubleshoot"
fi

echo ""
echo "=================================="
echo "✅ Diagnóstico concluído"
