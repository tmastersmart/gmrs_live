#!/bin/bash

# GMRS DNS Update Installer by lagmrs.com for Debian-based systems
# Installs files from https://github.com/tmastersmart/gmrs_live

# Exit on error
set -e


# Display copyright header and message
echo "==================================================="
echo "GMRS DNS and nodelist Update Installer  v1.0"
echo "(c)2023/2025 WRXB288 LAGMRS.com all rights reserved"
echo "Release date:8/23/2025"
echo "==================================================="
echo "This script installs a DNS registry service and a new NodeList update service"
echo "for GMRSHUB.com systems only."
echo ""
# Define URLs
SERVICE_URL="https://raw.githubusercontent.com/tmastersmart/gmrs_live/refs/heads/main/hub-files/gmrs-dns-update.service"
SCRIPT_URL="https://raw.githubusercontent.com/tmastersmart/gmrs_live/refs/heads/main/hub-files/dns_update.sh"
PHP_URL="https://raw.githubusercontent.com/tmastersmart/gmrs_live/refs/heads/main/hub-files/astdb.php"

# Define installation paths
TEMP_DIR="/tmp/gmrs-dns-update-install"
SERVICE_FILE="/etc/systemd/system/gmrs-dns-update.service"
SCRIPT_DIR="/usr/local/sbin"
SCRIPT_FILE="$SCRIPT_DIR/dns_update.sh"
PHP_FILE="$SCRIPT_DIR/astdb.php"

# Check for root privileges
if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run as root (use sudo)."
    exit 1
fi

# Check for systemd
if ! command -v systemctl &> /dev/null; then
    echo "Error: systemd is required but not found."
    exit 1
fi

# Install dependencies (curl, php-cli)
echo "Checking for curl and php-cli..."
apt-get update
if ! command -v curl &> /dev/null; then
    echo "Installing curl..."
    apt-get install -y curl
fi
if ! command -v php &> /dev/null; then
    echo "Installing php-cli..."
    apt-get install -y php-cli
fi
echo "Dependencies installed."

# Create temporary directory
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR"

# Download files
echo "Downloading files from GitHub..."
curl -sLO "$SERVICE_URL"
curl -sLO "$SCRIPT_URL"
curl -sLO "$PHP_URL"

# Create installation directory
mkdir -p "$SCRIPT_DIR"

# Install dns_update.sh
echo "Installing dns_update.sh to $SCRIPT_FILE..."
mv dns_update.sh "$SCRIPT_FILE"
chmod 755 "$SCRIPT_FILE"
chown root:root "$SCRIPT_FILE"

# Install astdb.php with backup
echo "Installing astdb.php to $PHP_FILE..."
if [ -f "$PHP_FILE" ] && [ ! -f "$PHP_FILE.old" ]; then
    echo "Backing up existing $PHP_FILE to $PHP_FILE.old..."
    mv "$PHP_FILE" "$PHP_FILE.old"
fi
mv astdb.php "$PHP_FILE"
chmod 644 "$PHP_FILE"
chown root:root "$PHP_FILE"

# Install service file
echo "Installing gmrs-dns-update.service to $SERVICE_FILE..."
mv gmrs-dns-update.service "$SERVICE_FILE"
chmod 644 "$SERVICE_FILE"
chown root:root "$SERVICE_FILE"

# Reload systemd, enable, and start service
echo "Enabling and starting gmrs-dns-update.service..."
systemctl daemon-reload
systemctl enable gmrs-dns-update.service
systemctl start gmrs-dns-update.service

# Verify service
if systemctl is-active --quiet gmrs-dns-update.service; then
    echo "Service started successfully."
else
    echo "Error: Service failed to start. Check logs with 'journalctl -u gmrs-dns-update.service'."
    exit 1
fi

# Update cron job to run astdb.php 4 times a day (12AM, 6AM, 12PM, 6PM) as root
echo "Updating cron job to run astdb.php 4 times daily..."
CRON_JOB="0 0,6,12,18 * * * php $PHP_FILE >/dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "$PHP_FILE" || true; echo "$CRON_JOB") | crontab -

# Clean up
cd /
rm -rf "$TEMP_DIR"

echo "Installation complete."
echo "Files installed:"
echo "  - $SERVICE_FILE (root:root, 644)"
echo "  - $SCRIPT_FILE (root:root, 755)"
echo "  - $PHP_FILE (root:root, 644)"
echo "Service enabled and running: gmrs-dns-update.service (runs dns_update.sh)."
echo "Cron job set to run $PHP_FILE at 12AM, 6AM, 12PM, 6PM as root with output to /dev/null."
echo "Existing cron jobs for $PHP_FILE have been replaced."
echo "Check service status with: systemctl status gmrs-dns-update.service"
