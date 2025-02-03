#!/usr/bin/env bash

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
cd "${SCRIPT_DIR}" || exit

docker compose up -d nginx
bash ./ssl-update.sh
docker compose down nginx

curl \
    -o "${SCRIPT_DIR}/../data/certbot/mozilla-dhparam.txt" \
    https://ssl-config.mozilla.org/ffdhe2048.txt

if [ -d /etc/cron.d ]; then
    cat >/etc/cron.d/certbot.cron <<EOF
# Run Let's Encrypt certbot renew twice a day at random times
0 0,12 * * *    root    /usr/bin/python -c 'import random; import time; time.sleep(random.random() * 3600)' && $SCRIPT_DIR/ssl-update.sh
EOF
fi
