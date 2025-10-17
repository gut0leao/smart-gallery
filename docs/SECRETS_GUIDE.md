# 🔐 Guia de Configuração de Secrets - Smart Gallery

Este guia explica **todos os secrets** necessários para o projeto Smart Gallery funcionar corretamente.

## 📋 Resumo dos Secrets

| Secret | Tipo | Obrigatório | Criado por |
|--------|------|-------------|------------|
| `GCP_SA_KEY` | Manual | ✅ Sim | Você (GCP Console) |
| `WP_DB_PASSWORD` | Manual | ✅ Sim | Você (criar senha) |
| `DUCKDNS_TOKEN` | Manual | ⚠️ Opcional | Você (DuckDNS) |
| `GH_PAT` | Manual | ⚠️ Opcional | Você (GitHub) |
| `VM_SSH_PRIVATE_KEY` | Automático | ✅ Sim | Workflow 02 |
| `GCP_SA_KEY_B64` | Desnecessário | ❌ Não | Remover |

---

## 1️⃣ Secrets MANUAIS (você precisa criar)

### 🔑 `GCP_SA_KEY` (Obrigatório)
**O que é:** Credenciais JSON da Service Account do Google Cloud Platform

**Como obter:**

1. Acesse o [Google Cloud Console](https://console.cloud.google.com)
2. Vá para **IAM & Admin** → **Service Accounts**
3. Clique em **+ CREATE SERVICE ACCOUNT**
4. Configure:
   - **Name:** `smart-gallery-github-actions`
   - **Description:** `Service account for GitHub Actions workflows`
5. Clique em **CREATE AND CONTINUE**
6. Adicione as seguintes roles:
   - `Compute Admin` (ou `Compute Instance Admin`)
   - `Service Account User`
   - `Storage Admin` (se usar Cloud Storage)
7. Clique em **DONE**
8. Na lista de Service Accounts, clique nos **3 pontinhos** da conta criada → **Manage Keys**
9. Clique em **ADD KEY** → **Create new key**
10. Escolha **JSON** e clique em **CREATE**
11. O arquivo JSON será baixado automaticamente

**Como adicionar no GitHub:**
1. Vá em: `GitHub → Seu Repositório → Settings → Secrets and variables → Actions`
2. Clique em **New repository secret**
3. **Name:** `GCP_SA_KEY`
4. **Value:** Cole TODO o conteúdo do arquivo JSON baixado (incluindo `{` e `}`)
5. Clique em **Add secret**

**Exemplo do conteúdo (NÃO use este, use o seu):**
```json
{
  "type": "service_account",
  "project_id": "seu-projeto-123456",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "smart-gallery-github-actions@seu-projeto.iam.gserviceaccount.com",
  ...
}
```

---

### 🔐 `WP_DB_PASSWORD` (Obrigatório)
**O que é:** Senha do banco de dados WordPress/MariaDB

**Como criar:**

1. Gere uma senha forte (recomendado: 16-32 caracteres, alfanuméricos)
   
   **Opção 1 - Usar um gerador online:**
   - https://passwordsgenerator.net/
   - Configure: 16-32 caracteres, letras + números (sem símbolos especiais)

   **Opção 2 - Gerar via terminal:**
   ```bash
   openssl rand -base64 24
   ```
   ou
   ```bash
   openssl rand -hex 16
   ```

2. **Guarde essa senha em um lugar seguro** (gerenciador de senhas)

**Como adicionar no GitHub:**
1. Vá em: `GitHub → Seu Repositório → Settings → Secrets and variables → Actions`
2. Clique em **New repository secret**
3. **Name:** `WP_DB_PASSWORD`
4. **Value:** Cole a senha que você gerou
5. Clique em **Add secret**

**⚠️ IMPORTANTE:** Use apenas letras e números (a-z, A-Z, 0-9). Evite símbolos especiais (`!@#$%`) para evitar problemas com escape de caracteres no SQL.

---

### 🦆 `DUCKDNS_TOKEN` (Opcional - só se usar DuckDNS)
**O que é:** Token de autenticação do DuckDNS para atualização automática de DNS

**Quando usar:** Se você escolher usar DuckDNS no workflow 02 (campo `use_duckdns: true`)

**Como obter:**

1. Acesse https://www.duckdns.org
2. Faça login (via Google, GitHub, etc.)
3. Na página principal, você verá seu **token** no topo da página
4. Copie o token (uma string longa tipo: `a7c4d0de-1234-5678-90ab-cdef12345678`)

**Como adicionar no GitHub:**
1. Vá em: `GitHub → Seu Repositório → Settings → Secrets and variables → Actions`
2. Clique em **New repository secret**
3. **Name:** `DUCKDNS_TOKEN`
4. **Value:** Cole o token do DuckDNS
5. Clique em **Add secret**

**✅ Você já tem este configurado!**

---

### 🔓 `GH_PAT` (Opcional - mas recomendado)
**O que é:** Personal Access Token do GitHub com permissões elevadas

**Por que usar:** O `GITHUB_TOKEN` padrão tem limitações. O PAT permite:
- Criar/atualizar GitHub Variables
- Criar/atualizar GitHub Secrets
- Fazer operações avançadas via API

**Como criar:**

1. Acesse https://github.com/settings/tokens
2. Clique em **Generate new token** → **Generate new token (classic)**
3. Configure:
   - **Note:** `Smart Gallery Workflows`
   - **Expiration:** 90 days (ou No expiration se preferir)
   - **Select scopes:** Marque:
     - ✅ `repo` (Full control of private repositories)
     - ✅ `workflow` (Update GitHub Action workflows)
     - ✅ `admin:org` → `write:org` (se for organização)
4. Clique em **Generate token**
5. **Copie o token imediatamente** (você não poderá ver novamente!)

**Como adicionar no GitHub:**
1. Vá em: `GitHub → Seu Repositório → Settings → Secrets and variables → Actions`
2. Clique em **New repository secret**
3. **Name:** `GH_PAT`
4. **Value:** Cole o token do GitHub
5. Clique em **Add secret**

**Alternativa:** Se não criar o PAT, os workflows usarão o `GITHUB_TOKEN` padrão (com limitações).

---

## 2️⃣ Secrets AUTOMÁTICOS (criados pelos workflows)

### 🔑 `VM_SSH_PRIVATE_KEY` (Criado automaticamente)
**O que é:** Chave SSH privada para acessar a VM do GCP

**Criado por:** Workflow **02-provision-infra.yml** (Provision Infrastructure)

**Como funciona:**
1. Quando você executa o workflow "2. Provision Infrastructure" pela primeira vez
2. O workflow gera automaticamente um par de chaves SSH (pública + privada)
3. A chave **privada** é armazenada no secret `VM_SSH_PRIVATE_KEY`
4. A chave **pública** é adicionada aos metadados da VM
5. Todos os workflows subsequentes usam essa chave para SSH

**⚠️ PROBLEMA ATUAL:** 
- Você disse que este secret está vazio
- Isso explica por que o workflow 03 falha ao conectar via SSH
- **Solução:** Execute novamente o workflow "2. Provision Infrastructure"

**Verificação:**
```bash
# Via GitHub CLI
gh secret list | grep VM_SSH_PRIVATE_KEY
```

---

## 3️⃣ Secrets DESNECESSÁRIOS (pode remover)

### ❌ `GCP_SA_KEY_B64`
**O que era:** Versão em base64 do `GCP_SA_KEY`

**Status:** Desnecessário - os workflows usam diretamente o `GCP_SA_KEY` em JSON

**Como remover:**
```bash
gh secret remove GCP_SA_KEY_B64
```

Ou via interface:
1. Vá em: `GitHub → Settings → Secrets and variables → Actions`
2. Clique em `GCP_SA_KEY_B64` → **Remove secret**

---

## 📊 Checklist de Configuração

### ✅ Secrets que VOCÊ deve criar agora:

- [ ] **GCP_SA_KEY** (via Google Cloud Console)
  - Acesse: https://console.cloud.google.com/iam-admin/serviceaccounts
  - Crie Service Account com roles: Compute Admin, Service Account User
  - Baixe chave JSON
  - Adicione em GitHub Secrets

- [ ] **WP_DB_PASSWORD** (gerar senha forte)
  - Gere: `openssl rand -hex 16`
  - Adicione em GitHub Secrets

- [ ] **DUCKDNS_TOKEN** (se usar DuckDNS)
  - ✅ Você já tem configurado!

- [ ] **GH_PAT** (opcional mas recomendado)
  - Acesse: https://github.com/settings/tokens
  - Gere token com scopes: `repo`, `workflow`
  - Adicione em GitHub Secrets

### ✅ Secrets criados automaticamente:

- [ ] **VM_SSH_PRIVATE_KEY**
  - Será criado ao executar workflow "2. Provision Infrastructure"
  - ⚠️ Execute este workflow primeiro!

### ✅ Limpeza:

- [ ] Remover **GCP_SA_KEY_B64** (desnecessário)

---

## 🚀 Ordem de Execução Recomendada

1. **Configure os secrets manuais:**
   - `GCP_SA_KEY` (obrigatório)
   - `WP_DB_PASSWORD` (obrigatório)
   - `GH_PAT` (recomendado)
   - `DUCKDNS_TOKEN` (se usar DuckDNS - já configurado ✅)

2. **Execute os workflows nesta ordem:**
   1. ✅ Workflow 01: Cleanup GCP (opcional - limpar recursos antigos)
   2. ✅ Workflow 02: Provision Infrastructure (cria VM e `VM_SSH_PRIVATE_KEY`)
   3. ✅ Workflow 03: Install Packages (agora deve funcionar!)
   4. ✅ Workflow 04: Configure Environment
   5. ✅ Workflow 05: Deploy Smart Gallery
   6. ✅ Workflow 06: Show Infrastructure Info

---

## 🔧 Resolução de Problemas

### Problema: Workflow 03 falha com "SSH connection failed"
**Causa:** Secret `VM_SSH_PRIVATE_KEY` está vazio

**Solução:**
1. Execute o workflow "2. Provision Infrastructure" novamente
2. Ele criará automaticamente o secret `VM_SSH_PRIVATE_KEY`
3. Depois execute o workflow "3. Install Packages"

### Problema: "Permission denied" ao criar GitHub Variables
**Causa:** `GITHUB_TOKEN` padrão tem permissões limitadas

**Solução:**
1. Crie um Personal Access Token (PAT) com scope `repo` e `workflow`
2. Adicione como secret `GH_PAT`
3. Os workflows usarão automaticamente o PAT quando disponível

### Problema: Não consigo obter `GCP_SA_KEY`
**Causa:** Você precisa de permissões de admin no projeto GCP

**Solução:**
1. Peça ao admin do projeto GCP para criar a Service Account
2. Ou peça permissão `Service Account Admin` no projeto
3. Siga os passos da seção `GCP_SA_KEY` acima

---

## 📞 Suporte

Se precisar de ajuda:
1. Verifique os logs do workflow no GitHub Actions
2. Execute o script de diagnóstico: `./scripts/diagnose-vm.sh`
3. Verifique a documentação do projeto em `/docs`

---

**Última atualização:** 2025-10-17
