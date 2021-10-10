# Standard Installation

This installation is meant for just **one** machine with a **LEMP** stack. 

1) Install the following dependencies `nginx php-fpm php-xml php-curl php-mysql php-mbstring php-pear composer mysql-server`
2) Secure MySQL server
   - Run the command `mysql_secure_installation` and remove anonymous users, disable root login and remove the test database
3) Copy Repo contents to `/var/www/ctfx/`
   - Run `composer install --no-dev --optimize-autoloader` under /var/www/ctfx
   - Make the folder `writable` writable (`sudo chown -R www-data:www-data writeable/`)
4) Setup Nginx
   - Copy the recommended Nginx config `install/recommended_nginx_config` to `/etc/nginx/nginx.conf`. Remember to change the `fastcgi_pass` entry to your current PHP version
   - Restart Nginx
5) Setup MySQL. 
   - `sudo` into `mysql` and run the following queries:
     - `CREATE DATABASE mellivora CHARACTER SET utf8 COLLATE utf8_general_ci;`
     - `CREATE USER 'mellivora'@'%' IDENTIFIED BY 'mellivora_pass';` Please ensure you change the default username and password
     - `GRANT ALL PRIVILEGES ON mellivora.* TO 'mellivora'@'%';  `
     - `SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));`
     - `exit`
   - `sudo mysql < install/sql/001-mellivora.sql`
   - `sudo mysql < install/sql/002-countries.sql`
6) Configuration
   - Copy `include/config/db.default.inc.php` to `include/config/db.inc.php` and configure. 
   - Copy `include/config/config.default.inc.php` to `include/config/config.inc.php` and configure. 
7) Create Admin User
   - Register your admin account on the website (and enable 2FA Authentication preferably)
   - Logout of your account
   - sudo into `mysql` and run the query `USE mellivora; UPDATE users SET class=100 WHERE id=1;`

This installation does not cover configuring SSL. To configure SSL, change the `nginx.conf` and setup a new configuration file under `sites-enabled/`. 


