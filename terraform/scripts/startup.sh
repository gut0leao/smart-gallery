#!/bin/bash
# Startup script for Smart Gallery VM
# This script runs when the VM first boots up

set -e

# Log everything
exec > >(tee /var/log/startup-script.log)
exec 2>&1

echo "🚀 Smart Gallery VM Startup Script - $(date)"
echo "Domain: ${domain_name}"
echo "Environment: ${environment}"

# Update system
echo "📦 Updating system packages..."
apt-get update -y
apt-get upgrade -y

# Install basic tools
echo "🔧 Installing basic tools..."
apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release

# Create deployment user
echo "👤 Creating deployment user..."
if ! id "deploy" &>/dev/null; then
    useradd -m -s /bin/bash deploy
    usermod -aG sudo deploy
    mkdir -p /home/deploy/.ssh
    chown deploy:deploy /home/deploy/.ssh
    chmod 700 /home/deploy/.ssh
fi

# Setup firewall (UFW)
echo "🔒 Setting up firewall..."
ufw --force enable
ufw allow ssh
ufw allow http
ufw allow https

# Create directories for applications
echo "📁 Creating application directories..."
mkdir -p /var/www/html
mkdir -p /var/log/smart-gallery
chown www-data:www-data /var/www/html
chmod 755 /var/www/html

# Set timezone
echo "🕐 Setting timezone..."
timedatectl set-timezone UTC

# Enable automatic updates
echo "🔄 Enabling automatic security updates..."
echo 'APT::Periodic::Update-Package-Lists "1";' > /etc/apt/apt.conf.d/20auto-upgrades
echo 'APT::Periodic::Unattended-Upgrade "1";' >> /etc/apt/apt.conf.d/20auto-upgrades

# Install fail2ban for security
echo "🛡️ Installing fail2ban..."
apt-get install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban

echo "✅ Startup script completed successfully!"
echo "🎯 VM is ready for package installation phase"

# Signal completion
touch /var/log/startup-completed