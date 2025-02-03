# INSTALLATION

## Check out code

```
cd ~
git clone git@github.com:zeek/zeek-pkg-web.git
cd zeek-pkg-web
```

## Copy files/directories
```
sudo cp -a bropkg /var/www
### Modify salt, database password, and CILogon client id/key in
###     secrets/.env to something suitable for your installation
cp secrets/.env /var/www/bropkg/config/
chmod 640 /var/www/bropkg/config/.env
cd /var/www/bropkg
composer update
sudo chgrp -R apache /var/www/bropkg
```

## Set up HTTPD
Edit /etc/httpd/conf.d/ssl.conf :
```
<VirtualHost _default_:443>
DocumentRoot "/var/www/bropkg"

...

<Directory />
  Options FollowSymLinks
  AllowOverride All
</Directory>

</VirtualHost>

```

Edit /etc/httpd/conf.d/virthost.conf :
```
<VirtualHost *:80>
DocumentRoot /var/www/bropkg

```

Restart httpd process:
```
sudo service httpd restart
```

## Initialize database

```
mysql_secure_installation    # only needed once

Enter current password for root (enter for none): <none>
OK, successfully used password, moving on...
Set root password? [Y/n] y
New password:
Re-enter new password:
Password updated successfully!
Reloading privilege tables..
 ... Success!
Remove anonymous users? [Y/n] y
 ... Success!
Disallow root login remotely? [Y/n] y
 ... Success!
Remove test database and access to it? [Y/n] y
 - Dropping test database...
 ... Success!
 - Removing privileges on test database...
 ... Success!
Reload privilege tables now? [Y/n] y
 ... Success!
Cleaning up...
All done!
```

Load bropkg user and associated tables. Change BRO_USER_PASSWORD to
something suitable for the installation.

```
mysql -u root -p < secrets/database.sql
```

## Set up cronjob to read Zeek pkg info
Note: Change USERNAME@HOSTNAME.ORG to the email that should receive emails
about the output of the bro-pkg-web-updater script.  Use of new Python 3
versions is also possible, this example just happens to use 3.4.

```
sudo su
yum install python34-pip
pip-3.4 install zkg
python3 -m pip install https://github.com/zeek/zeek-package-ci/archive/master.zip
cp cronjob/bro-pkg-web-updater.php /usr/local/sbin/
chmod 700 /usr/local/sbin/bro-pkg-web-updater.php
echo 'MAILTO=USERNAME@HOSTNAME.ORG
# Update to latest zkg
0 4 * * *    root    pip-3.4 install --upgrade zkg
# Read the list of Bro packages and update database at 4am daily
0 4 * * *    root    /usr/local/sbin/bro-pkg-web-updater.php' > \
/etc/cron.d/bro-pkg-web.cron
exit
```
Run the script at least once!
```
sudo php /usr/local/sbin/bro-pkg-web-updater.php
```
