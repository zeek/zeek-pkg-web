#!/usr/bin/env bash

# This script is originally from https://gist.github.com/maxivak/4706c87698d14e9de0918b6ea2a41015
# with simplifications and adaptations for Zeek's uses.

# Edit these two values before running this script or init-certs.sh for the
# first time.
DOMAIN="example.com"
EMAIL="email@example.com"

# The owner/group of the cert files
CHOWN="root:root"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_PATH="${SCRIPT_DIR}/.."
CERT_DIR_PATH="${REPO_PATH}/data/certbot/letsencrypt"
WEBROOT_PATH="${REPO_PATH}/data/certbot/www"
CERTBOT_LOGS_PATH="${REPO_PATH}/data/certbot/logs"
LE_RENEW_HOOK="docker restart zeek-pkg-web-nginx-1"
EXP_LIMIT="30"
FIRST_RUN=0

if [[ -z $DOMAIN ]]; then
    echo "No domains set, please fill -e 'DOMAIN=example.com'"
    exit 1
fi

if [[ -z $EMAIL ]]; then
    echo "No email set, please fill -e 'EMAIL=your@email.tld'"
    exit 1
fi

if [[ -z $CERT_DIR_PATH ]]; then
    echo "No cert dir path set, please fill -e 'CERT_DIR_PATH=/etc/letsencrypt'"
    exit 1
fi

if [[ -z $WEBROOT_PATH ]]; then
    echo "No webroot path set, please fill -e 'WEBROOT_PATH=/tmp/letsencrypt'"
    exit 1
fi

if [[ -z $CERTBOT_LOGS_PATH ]]; then
    echo "No webroot path set, please fill -e 'CERTBOT_LOGS_PATH=/tmp/letsencrypt'"
    exit 1
fi

exp_limit="${EXP_LIMIT:-30}"

le_hook() {
    if [[ $FIRST_RUN -eq 1 ]]; then
        return
    fi

    echo "[INFO] Run: ${LE_RENEW_HOOK}"
    eval "$LE_RENEW_HOOK"
}

le_fixpermissions() {
    echo "[INFO] Fixing permissions"
    mkdir -p "${CERT_DIR_PATH}"
    chown -R "${CHOWN}" "${CERT_DIR_PATH}"
    find "${CERT_DIR_PATH}" -type d -exec chmod 755 {} \;
    find "${CERT_DIR_PATH}" -type f -exec chmod "${CHMOD:-644}" {} \;
}

le_renew() {
    docker run --rm --name temp_certbot \
        -v "${CERT_DIR_PATH}:/etc/letsencrypt" \
        -v "${WEBROOT_PATH}:/tmp/letsencrypt" \
        -v "${CERTBOT_LOGS_PATH}:/var/log" \
        certbot/certbot:v3.1.0 certonly \
        --webroot --agree-tos --renew-by-default --non-interactive \
        --preferred-challenges http-01 \
        --server https://acme-v02.api.letsencrypt.org/directory --text \
        --email "${EMAIL}" -w /tmp/letsencrypt --domain "${DOMAIN}"

    le_fixpermissions
    le_hook
}

le_check() {
    cert_file="$CERT_DIR_PATH/live/${DOMAIN}/fullchain.pem"

    echo "START check"
    echo "file: $cert_file"

    if [[ -e $cert_file ]]; then

        exp=$(date -d "$(openssl x509 -in "${cert_file}" -text -noout | grep "Not After" | cut -c 25-)" +%s)
        datenow=$(date -d "now" +%s)
        days_exp=$(((exp - datenow) / 86400))

        echo "Checking expiration date for ${DOMAIN}..."

        if [ "$days_exp" -gt "$exp_limit" ]; then
            echo "The certificate is up to date, no need for renewal ($days_exp days left)."
        else
            echo "The certificate for ${DOMAIN} is about to expire soon. Starting webroot renewal script..."
            le_renew
            echo "Renewal process finished for domain ${DOMAIN}"
        fi
    else
        FIRST_RUN=1

        echo "[INFO] certificate file not found for domain ${DOMAIN}. Starting webroot initial certificate request script..."
        le_renew
        echo "Certificate request process finished for domain ${DOMAIN}"
    fi

}

echo "--- start. $(date)"

le_check
