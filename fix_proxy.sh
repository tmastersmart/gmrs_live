#!/bin/bash
# ===============================================
# Hamvoip Apache Proxy Module Fix Script
# Author: The Master
# Purpose: Disable unsafe proxy modules in Apache
# Tested on Hamvoip/Debian-based nodes
# Usage: curl -sSL <URL> | bash
# ===============================================

CONFIG_FILE="/etc/httpd/conf/httpd.conf"
BACKUP_FILE="${CONFIG_FILE}.bak.$(date +%Y%m%d%H%M%S)"

# Modules to disable
MODULES=(
    proxy_module
    proxy_connect_module
    proxy_ftp_module
    proxy_http_module
    proxy_fcgi_module
    proxy_scgi_module
    proxy_wstunnel_module
    proxy_ajp_module
    proxy_balancer_module
    proxy_express_module
)

echo "Backing up Apache config to $BACKUP_FILE ..."
cp "$CONFIG_FILE" "$BACKUP_FILE" || { echo "ERROR: Backup failed! Exiting."; exit 1; }

echo "Scanning for active proxy modules..."
CHANGES=0

for mod in "${MODULES[@]}"; do
    if grep -qE "^LoadModule[[:space:]]+$mod" "$CONFIG_FILE"; then
        echo " - Disabling $mod"
        sed -i "s/^LoadModule[[:space:]]\+$mod/#&/" "$CONFIG_FILE"
        ((CHANGES++))
    fi
done

if [ $CHANGES -gt 0 ]; then
    echo "Proxy modules disabled: $CHANGES"
    echo "Restarting Apache..."
    systemctl restart httpd
    echo "Done! You may want to reboot the node."
else
    echo "No active proxy modules found. Nothing to do."
fi
