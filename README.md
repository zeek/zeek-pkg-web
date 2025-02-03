# INSTALLATION

## Check out code

```
cd ~
git clone git@github.com:zeek/zeek-pkg-web.git
cd zeek-pkg-web
```

## Edit secrets/.env

`secrets/.env` has a set of variables for passwords and such that PHP will need
to connect to the database and update the packages list from GitHub.

## Initialize an SSL certificate

- Edit `cert_setup/ssl-update.sh` and set the `DOMAINS` and `EMAIL` values to
  be sane for your installation.
- Run the `cert_setup/init-certs.sh` script. This will generate a Let's Encrypt
  certificate, store it in the location that nginx container will use, and add
  a cron task to automatically update it.
- Edit `docker/nginx-default.conf` and set the hostname in the `ssl_certificate`
  and `ssl_certificate_key` values to match the `DOMAINS` setting from earlier.

## (For development only) Enable the database container

- Edit `docker-compose.yml` and uncomment the section for the `db` service
- Edit `secrets/database.sql` and change the `BRO_USER_PASSWORD` value to match
  what is set in `secrets.env`.
- Edit `secrets/.env` and change the `DB_HOST` value to `db` to map to the
  internal hostname for the docker database service.

## Run `docker-compose`

```
docker-compose build
docker compose up -d
```

This will create the images needed for nginx and PHP and start them running. The
Dockerfiles for these images are stored in the `docker` directory. This will
also create a Let's Encrypt cert based on the hostname set in the

## (Optional) Run an update of the packages database

```
docker exec -it zeek-pkg-web-php-1 /bin/bash
/etc/cron.daily/bro-pkg-web-cron.sh
```
