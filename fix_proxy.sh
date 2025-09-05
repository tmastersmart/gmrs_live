#!/bin/bash
# ===============================================
# Hamvoip Apache Proxy Module Hardening Script
# ===============================================
# Author: WRXB288
# Copyright: lagmrs.com Louisiana Nationwide
# ===============================================
# Purpose:
#   This script disables unsafe Apache proxy modules 
#   (proxy, proxy_http, proxy_ftp, proxy_connect) 
#   on Hamvoip/Debian-based nodes to improve security.
# ===============================================

# Display header to the user
echo ""
echo ""
echo ""
echo ""
echo ""
echo "=================================================="
echo "GMRS Hamvoip Apache Proxy Module Hardening Script"
echo "=================================================="
echo "Author: WRXB288"
echo "Copyright: 2025 LAGMRS.com LA2way.com "
echo "V1.0 release 9/4/25"
echo "Purpose:"
echo "  This script disables unsafe Apache proxy modules"
echo "  (proxy, proxy_http, proxy_ftp, proxy_connect)"
echo "  on Hamvoip/Debian-based nodes to improve security."
echo "==================================================="
echo "Press any key to continue..."
# Wait for user input
read -n 1 -s -r
echo ""


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
    echo "No active proxy modules found. Your safe your image is patched."
fi
