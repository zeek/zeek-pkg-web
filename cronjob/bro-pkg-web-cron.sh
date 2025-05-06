#!/bin/sh

# Update zkg to the latest version
pip3 install --upgrade zkg

# Grab all of the packages, parse them, and update the database
/usr/local/sbin/bro-pkg-web-updater.php
