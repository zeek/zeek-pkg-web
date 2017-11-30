# INSTALLATION

## Checkout code

```
cd ~
git clone git@git-csd.ncsa.illinois.edu:tfleury/bropkgweb.git
cd bropkgweb

```

## Copy files/directories
```
sudo cp -a bropkg /var/www
sudo chgrp apache /var/www/bropkg
sudo cp secrets/.env /var/www/bropkg/config
```

## Set up HTTPD
Edit /etc/httpd/conf.d/ssl.conf :
```
<VirtualHost _default_:443>
DocumentRoot "/var/www/bropkg"
...
</VirtualHost>

```

Edit /etc/httpd/conf.d/virthost.conf :
```
<VirtualHost *:80>
DocumentRoot /var/www/bropkg

```

Restart httpd process:
```
service httpd restart
```

## Initialize database
Database root password is in the Shared-security-certauth LastPass folder.

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

Load bropkg user and associated tables.
```
mysql -u root -p < secrets/database.sql
```

## Set up cronjob to read bro pkg info
Note: Change USERNAME@HOSTNAME.ORG to the email that should receive emails
about the output of the bro-pkg-web-updater script.

```
pip install bro-pkg
sudo cp cronjob/bro-pkg-web-updater.php /usr/local/sbin/
sudo chmod 700 /usr/local/sbin/bro-pkg-web-updater.php
echo 'MAILTO=USERNAME@HOSTNAME.ORG
# Read the list of Bro packages and update database at 4am daily
0 4 * * *    root    /usr/local/sbin/bro-pkg-web-updater.php' > 
/etc/cron.d/bro-pkg-web.cron
```
Run the script at least once!
```
php /usr/local/sbin/bro-pkg-web-updater.php
```

