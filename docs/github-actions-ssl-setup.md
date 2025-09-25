# Configuração do GitHub Actions com Let's Encrypt

Este documento explica como configurar as GitHub Secrets necessárias para o workflow de deploy automático com SSL/HTTPS.

## 📋 GitHub Secrets Necessárias

Acesse `Settings → Secrets and variables → Actions` no seu repositório GitHub e adicione:

### 🔐 **GCP (Google Cloud Platform)**
- **`GCP_SA_KEY`**: JSON da chave da conta de serviço do Google Cloud
- **`GCP_PROJECT_ID`**: ID do projeto no Google Cloud  
- **`GCP_VM_INSTANCE`**: Nome da instância da VM (ex: `smart-gallery-vm`)
- **`GCP_VM_ZONE`**: Zona da VM (ex: `us-central1-a`)

### 🌐 **Site e Domínio**  
- **`SITE_URL`**: URL completa HTTPS do site (ex: `https://smartgallery.exemplo.com`)
- **`DOMAIN_NAME`**: Apenas o domínio (ex: `smartgallery.exemplo.com`)
- **`LETSENCRYPT_EMAIL`**: Email para registro do certificado Let's Encrypt

## 🚀 Como Configurar

### 1. Configurar Google Cloud

```bash
# Criar conta de serviço
gcloud iam service-accounts create smart-gallery-deploy \
  --display-name="Smart Gallery Deploy"

# Dar permissões necessárias
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
  --member="serviceAccount:smart-gallery-deploy@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
  --role="roles/compute.instanceAdmin.v1"

# Criar chave JSON
gcloud iam service-accounts keys create key.json \
  --iam-account=smart-gallery-deploy@YOUR_PROJECT_ID.iam.gserviceaccount.com
```

### 2. Configurar VM no GCP

A VM deve ter:
- **SO**: Ubuntu 20.04 LTS ou superior
- **Software**: Apache, PHP, MySQL, WordPress
- **Firewall**: Permitir HTTP (80) e HTTPS (443)
- **DNS**: Domínio apontando para o IP da VM

### 3. Adicionar Secrets no GitHub

1. Vá para `https://github.com/SEU_USUARIO/smart-gallery/settings/secrets/actions`
2. Clique em **"New repository secret"**
3. Adicione cada secret da lista acima

### 4. Configurar DNS

Antes do primeiro deploy, configure o DNS:

```
# Tipo A
smartgallery.exemplo.com → IP_DA_VM_GCP
```

## 🔒 Funcionamento do Let's Encrypt

O workflow automaticamente:

1. **Instala Certbot** na VM se não estiver instalado
2. **Obtém certificado SSL** para o domínio configurado  
3. **Configura Apache** para redirecionar HTTP → HTTPS
4. **Programa renovação** automática via crontab
5. **Testa a configuração** SSL após o deploy

### Renovação Automática

Um crontab é configurado automaticamente:
```bash
0 12 * * * /usr/bin/certbot renew --quiet
```

## 🧪 Teste Local

Para testar localmente sem deploy:

```bash
# Verificar se o domínio resolve
nslookup smartgallery.exemplo.com

# Testar conectividade
curl -I http://smartgallery.exemplo.com
curl -I https://smartgallery.exemplo.com
```

## 🚨 Troubleshooting

### Erro: "Domain validation failed"
- Verifique se DNS está apontando para o IP correto
- Aguarde propagação DNS (pode levar até 48h)
- Certifique-se que a VM permite tráfego nas portas 80 e 443

### Erro: "Apache configuration test failed"  
- Verifique se Apache está rodando: `sudo systemctl status apache2`
- Teste configuração: `sudo apache2ctl configtest`

### Erro: "Certificate already exists"
- O Certbot detecta certificados existentes automaticamente
- Use `sudo certbot certificates` na VM para verificar

## 📱 Monitoramento

Após o deploy, verifique:

✅ **Site carrega via HTTPS**: `https://smartgallery.exemplo.com`  
✅ **HTTP redireciona**: `http://smartgallery.exemplo.com` → `https://`  
✅ **Certificado válido**: Sem avisos no navegador  
✅ **Plugin ativo**: WordPress admin → Plugins  

## 🔄 Próximos Deploys

Após a configuração inicial, os deploys subsequentes:
- Renovam certificados automaticamente se necessário
- Mantêm a configuração SSL existente  
- Aplicam apenas updates do plugin

---

**Dúvidas?** Verifique os logs do GitHub Actions ou a documentação do Certbot.