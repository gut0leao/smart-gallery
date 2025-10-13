# GCP Cleanup Script - Guia de Uso

Este script faz uma limpeza abrangente de **TODOS** os recursos do Google Cloud Platform em um projeto específico.

## ⚠️ **AVISO IMPORTANTE**

**Este script pode DELETAR PERMANENTEMENTE todos os recursos do seu projeto GCP, incluindo:**
- VMs e discos
- Bancos de dados Cloud SQL  
- Buckets do Cloud Storage e seus dados
- Clusters GKE
- Serviços Cloud Run/Functions
- Service Accounts personalizadas
- Redes e IPs estáticos

**Use apenas em projetos de teste/desenvolvimento!**

## 🚀 **Como Usar**

### 1. Pré-requisitos

**Instalar gcloud CLI:**
```bash
# Instalar gcloud CLI se não tiver
curl https://sdk.cloud.google.com | bash
exec -l $SHELL
```

**Configurar credenciais GCP:**
O script agora verifica automaticamente as credenciais no diretório `.credentials/`. Se não encontrar, ele fornecerá instruções detalhadas para:

1. Criar uma Service Account no GCP Console
2. Configurar as permissões necessárias
3. Baixar a chave JSON
4. Salvar no diretório `.credentials/`

**Não é mais necessário fazer `gcloud auth login` manualmente!**

### 2. Executar o Script

```bash
# 1. DRY RUN (padrão) - apenas mostra o que seria deletado
./scripts/gcp-cleanup.sh

# 2. EXECUTAR REALMENTE - deleta os recursos
./scripts/gcp-cleanup.sh --real-run

# 3. MODO FORÇA (sem confirmações) - PERIGOSO!
./scripts/gcp-cleanup.sh --real-run --force
```

### 3. Opções Disponíveis

| Opção | Descrição |
|-------|-----------|
| `--dry-run` | Apenas mostra o que seria deletado (padrão) |
| `--real-run` | Executa as deleções realmente |
| `--force` | Pula confirmações (use com cuidado!) |
| `--help` | Mostra ajuda |

## 📋 **O Que o Script Limpa**

### 🖥️ **Compute Engine**
- ✅ Instâncias de VM
- ✅ Discos persistentes  
- ✅ Redes personalizadas (mantém VPC default)
- ✅ Regras de firewall personalizadas
- ✅ IPs estáticos regionais e globais

### 🪣 **Cloud Storage**
- ✅ Todos os buckets e seus conteúdos

### 🗄️ **Cloud SQL**
- ✅ Todas as instâncias de banco de dados

### ⚡ **Cloud Functions**
- ✅ Todas as funções

### 🏃 **Cloud Run**
- ✅ Todos os serviços

### ☸️ **Google Kubernetes Engine**
- ✅ Todos os clusters

### 👤 **IAM**
- ✅ Service accounts personalizadas
- ❌ Contas padrão do sistema (preservadas)

### 🔌 **APIs**
- ❌ APIs NÃO são desabilitadas automaticamente (por segurança)
- ℹ️ O script lista as APIs ativas para você decidir

## 🛡️ **Recursos Protegidos**

O script **NÃO** deleta:
- VPC padrão (`default`)
- Service accounts padrão do sistema
- APIs do Google (apenas lista)
- Projetos (apenas limpa recursos dentro)

## 💡 **Exemplos Práticos**

### Cenário 1: Verificar o que seria deletado
```bash
./scripts/gcp-cleanup.sh
# Mostra todos os recursos sem deletar nada
```

### Cenário 2: Limpeza completa de projeto de teste
```bash
./scripts/gcp-cleanup.sh --real-run
# Executa deleções com confirmações
```

### Cenário 3: Configurar credenciais pela primeira vez
```bash
# Execute o script - ele detectará a ausência de credenciais
./scripts/gcp-cleanup.sh

# O script mostrará instruções completas para:
# 1. Criar Service Account no GCP
# 2. Configurar permissões
# 3. Baixar chave JSON
# 4. Salvar em .credentials/
```

### Cenário 4: Limpeza automatizada (CI/CD)
```bash
DRY_RUN=false FORCE=true ./scripts/gcp-cleanup.sh
# Para automação (use com extremo cuidado!)
```

## 🔍 **Verificação Pós-Limpeza**

Após executar, verifique no GCP Console:

1. **Compute Engine** → VM instances (deve estar vazio)
2. **Cloud Storage** → Browser (deve estar vazio)  
3. **Cloud SQL** → Instances (deve estar vazio)
4. **VPC Network** → External IP addresses (deve estar vazio)
5. **IAM & Admin** → Service Accounts (apenas contas padrão)

## 🚨 **Troubleshooting**

### Erro: "No GCP credentials found"
- Execute o script uma vez para ver as instruções de configuração
- Crie uma Service Account com as permissões necessárias
- Baixe a chave JSON e salve em `.credentials/`

### Erro: "Failed to authenticate with service account"
- Verifique se o arquivo JSON está correto e não corrompido
- Confirme se a Service Account tem todas as permissões listadas
- Tente baixar uma nova chave JSON

### Erro: "Permission denied" em operações
```bash
# Verificar conta ativa
gcloud auth list

# Verificar permissões no projeto
gcloud projects get-iam-policy PROJECT_ID
```

### Erro: "Resource in use"
- Alguns recursos têm dependências
- O script tenta deletar na ordem correta
- Execute novamente se alguns recursos não foram deletados na primeira vez

### Erro: "Billing required"
- Alguns recursos podem precisar de billing ativo para deleção
- Verifique se billing está habilitado no projeto

## ⏱️ **Tempo de Execução**

- **Dry run**: 1-2 minutos
- **Execução real**: 5-15 minutos (depende da quantidade de recursos)
- **Recursos grandes**: VMs e clusters GKE podem levar mais tempo

## 🔄 **Recuperação**

**IMPORTANTE**: Após executar este script com `--real-run`, **NÃO há como recuperar** os recursos deletados!

Certifique-se de:
- ✅ Fazer backup de dados importantes antes
- ✅ Exportar configurações importantes  
- ✅ Anotar informações de rede/DNS se necessário
- ✅ Verificar se não há recursos críticos no projeto

## 📞 **Suporte**

Se encontrar problemas:
1. Verifique os logs do script
2. Confirme permissões no GCP
3. Tente executar comandos `gcloud` individuais para depuração
4. Use modo `--dry-run` para entender o que o script fará