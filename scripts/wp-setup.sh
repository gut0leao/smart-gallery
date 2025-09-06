#!/bin/bash

# Smart Gallery Filter - WordPress Setup Script
# Este script configura WordPress do zero com plugins necessários

echo "🚀 Smart Gallery Filter - WordPress Setup"
echo "========================================"

# Verificar se estamos no DDEV
if ! command -v ddev &> /dev/null; then
    echo "❌ Error: DDEV not found. Please run this script from a DDEV environment."
    exit 1
fi

# Verificar se WordPress está acessível
if ! ddev exec wp core is-installed --quiet 2>/dev/null; then
    echo "ℹ️ WordPress not installed. Installing WordPress first..."
    ddev exec wp core install \
        --url="https://smart-gallery-filter.ddev.site" \
        --title="Smart Gallery Filter Demo" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com"
else
    echo "⚠️  WORDPRESS JÁ ESTÁ INSTALADO!"
    echo ""
    echo "🚨 ATENÇÃO: Este script fará uma instalação COMPLETA do zero:"
    echo "   • Todos os dados do WordPress serão PERDIDOS"
    echo "   • Banco de dados será RECRIADO"
    echo "   • Posts, páginas, usuários, configurações serão APAGADOS"
    echo "   • Arquivos de configuração serão SOBRESCRITOS"
    echo ""
    echo "💡 Se você quer apenas atualizar plugins/temas sem perder dados, cancele e:"
    echo "   • Use comandos individuais: ddev exec wp plugin install [plugin]"
    echo "   • Ou use o wp-admin para instalações manuais"
    echo ""
    echo "⚠️  Esta ação é IRREVERSÍVEL!"
    echo ""
    read -p "Tem certeza que quer REINSTALAR WordPress do zero? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "😌 Cancelado. Sua instalação WordPress permanece intacta."
        exit 0
    fi
    
    echo "🗑️ Removendo WordPress existente..."
    ddev exec wp db reset --yes
    
    echo "📦 Reinstalando WordPress..."
    ddev exec wp core install \
        --url="https://smart-gallery-filter.ddev.site" \
        --title="Smart Gallery Filter Demo" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com"
fi

echo ""
echo "🔌 Instalando plugins necessários..."

# Instalar Elementor
echo "   📦 Instalando Elementor..."
ddev exec wp plugin install elementor --activate

# Instalar Pods
echo "   📦 Instalando Pods Framework..."  
ddev exec wp plugin install pods --activate

# Ativar plugin principal
echo "   🎯 Ativando Smart Gallery Filter..."
ddev exec wp plugin activate smart-gallery-filter

echo ""
echo "🔧 Configurando HTTPS com mkcert..."
if command -v mkcert &> /dev/null; then
    mkcert -install
    mkcert smart-gallery-filter.ddev.site
    echo "   ✅ Certificados SSL criados"
else
    echo "   ⚠️ mkcert não encontrado. Instale para HTTPS automático:"
    echo "      https://github.com/FiloSottile/mkcert"
fi

echo ""
echo "✅ Setup completo!"
echo ""
echo "🌐 Acesse seu site em: https://smart-gallery-filter.ddev.site"
echo "🔑 Admin: https://smart-gallery-filter.ddev.site/wp-admin"
echo "   User: admin"
echo "   Pass: admin"
echo ""
echo "📋 Próximos passos:"
echo "1. Execute: ./scripts/pods-import.sh (para importar dados demo)"
echo "2. Configure seu widget Elementor"
echo "3. Teste as funcionalidades"
