#!/usr/bin/env bash

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
cd "${SCRIPT_DIR}" || exit

# Download the Mozilla parameters file and store it where nginx can get it
mkdir -p "${SCRIPT_DIR}/../data/certbot/"
curl \
    -o "${SCRIPT_DIR}/../data/certbot/mozilla-dhparam.txt" \
    https://ssl-config.mozilla.org/ffdhe2048.txt

if [ -z "$1" ]; then

    # PRODUCTION MODE

    # Generate certificates using Let's Encrypt
    bash ./ssl-update.sh

    # Add a cronjob that automatically updates the certs
    if [ -d /etc/cron.d ]; then
        cat >/etc/cron.d/certbot.cron <<EOF
# Run Let's Encrypt certbot renew twice a day at random times
0 0,12 * * *    root    /usr/bin/python -c 'import random; import time; time.sleep(random.random() * 3600)' && $SCRIPT_DIR/ssl-update.sh
EOF
    fi

elif [ "$1" == "dev" ]; then

    # DEV MODE

    # Generate a self-signed cert for testing
    mkdir -p "${SCRIPT_DIR}/../data/certbot/letsencrypt/live/example.com"
    openssl req -x509 -newkey ec -pkeyopt ec_paramgen_curve:secp384r1 -days 3650 -nodes \
        -keyout "${SCRIPT_DIR}/../data/certbot/letsencrypt/live/example.com/privkey.pem" \
        -out "${SCRIPT_DIR}/../data/certbot/letsencrypt/live/example.com/cert.pem" \
        -subj "/CN=example.com" -addext "subjectAltName=DNS:example.com,DNS:*.example.com"

fi
