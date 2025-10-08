#!/bin/sh

# Update zkg to the latest version
pip3 install --no-cache-dir --upgrade --break-system-packages zkg

# Grab all of the packages, parse them, and update the database
/usr/local/sbin/bro-pkg-web-updater.php
