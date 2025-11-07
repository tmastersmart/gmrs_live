GMRS HUB Server Files

GMRS DNS Update and NodeList System v1.0
(c)2023/2025 WRXB288 KJ5MZL LAGMRS.com all rights reserved
Release date: 8/23/2025  
This repository provides server files for GMRSHUB.com systems to enhance DNS registry and NodeList updates for GMRS hubs. It includes a DNS registry service (dns_update.sh) and a NodeList update service (astdb.php), addressing issues like server crashes, corrupted NodeLists, and invalid data.
Components
astdb.php
A drop-in replacement for the NodeList update system, designed to:

Fix NULL issues on boot by validating updates.
Store backups to prevent data loss.
Sanitize input to block invalid NodeList data.
Trim long names for Supermon display, preserving emoticons (e.g., 😊, ★, 日本語).
Create multiple NodeLists: hubs-only, unmodified, and backup.
Run via cron multiple times daily, updating only when needed.

Path: /usr/local/sbin/astdb.phpCron: 0 0,6,12,18 * * * php /usr/local/sbin/astdb.php >/dev/null 2>&1 (12AM, 6AM, 12PM, 6PM)
dns_update.sh
A DNS registry updater (not a NodeList, despite previous misnaming) running as a background service. It:

Replaces the outdated “ast” service (misnamed as “nodelist”).
Creates backups and logs errors for debugging.
Runs continuously via gmrs-dns-update.service.

Path: /usr/local/sbin/dns_update.shService: /etc/systemd/system/gmrs-dns-update.service
install-gmrs-dns-update.sh
Installs all components on Debian-based systems, including:

Downloading files from this repository.
Installing dns_update.sh and astdb.php to /usr/local/sbin/ (owned by root:root).
Backing up existing astdb.php to astdb.php.old (once only).
Installing and enabling gmrs-dns-update.service.
Setting up the cron job for astdb.php.
Installing dependencies (curl, php-cli).

Important: Stop the old registory service (misnamed) as a nodelist service before installation:
Why they call it a nodelist service I dont know because nodelist is handeled by astdb.php.  It is a registry service.

If your install script asked you .you can select not to install it. But if you installed it you need to find it and uninstall it.
If might be called nodelist.service

sudo systemctl stop nodelist.service
sudo systemctl disable nodelist.service


Or on some installs it might be running from cron.  its a .sh script not the php file


Installation
Option 1: Single Command (Direct)
Run the installer directly from GitHub:
curl -sL https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/hub-files/install-gmrs-dns-update.sh | sudo bash

Option 2: Download and Inspect (Safer)
Download the script to /tmp, inspect it, then run:
cd /tmp
curl -sLO https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/hub-files/install-gmrs-dns-update.sh
cat install-gmrs-dns-update.sh  # Review the script
sudo bash install-gmrs-dns-update.sh

Post-Installation

Verify Files:ls -l /etc/systemd/system/gmrs-dns-update.service
ls -l /usr/local/sbin/dns_update.sh
ls -l /usr/local/sbin/astdb.php


Check Service:systemctl status gmrs-dns-update.service


Check Cron:crontab -l


Should show: 0 0,6,12,18 * * * php /usr/local/sbin/astdb.php >/dev/null 2>&1


Test NodeList Update:php /usr/local/sbin/astdb.php





Notes

Dependencies: Requires curl and php-cli (installed automatically).
Ownership: All files are owned by root:root.
Cron: Replaces existing astdb.php cron jobs for consistency.
Logs: dns_update.sh logs errors (check /usr/local/sbin/dns_update.sh for log path). astdb.php cron output is discarded (/dev/null).
Safety: Use prepared statements in astdb.php for database operations:$stmt = $pdo->prepare("INSERT INTO devices (id, name) VALUES (?, ?)");
$stmt->execute([$u[0], clean_input($u[1])]);



For issues or contributions, contact WRXB288 via LAGMRS.com.
