# Scalable Deployment

It should be noted that this deployment method has been tested only with Digital Ocean but should be similar for other VPS. 

## Node Installation 


1) Install the following dependencies `nginx php-fpm php-xml php-curl php-mysql php-mbstring php-pear composer php-redis`

2) Copy repo contents to `/var/www/ctfx`
3) Run `composer install --no-dev --optimize-autoloader` under `/var/www/ctfx`
4) Make the folder `writeable` writable (`sudo chown -R www-data:www-data writeable/`)
5) Copy the recommended Nginx config `install/recommended_nginx_config` to `/etc/nginx/nginx.conf`. Remember to change the `fastcgi_pass` entry to your current PHP version
6) Restart Nginx

Do note that the current `nginx.conf` is not configured for SSL. To enable SSL, you may wish to make a new `nginx.conf` and a separate config file under `sites-available` . 

## MySQL Configuration

This should be done on a separate droplet or machine that is within the same private network as Redis and the various nodes

1) `sudo` into `mysql` and run the following queries:
   - `CREATE DATABASE mellivora CHARACTER SET utf8 COLLATE utf8_general_ci;`
   - `CREATE USER 'mellivora'@'%' IDENTIFIED WITH mysql_native_password BY 'mellivora_pass';` Please ensure you change the default username and password
   - `GRANT ALL PRIVILEGES ON mellivora.* TO 'mellivora'@'%';  `
   - `SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));`
   - `exit`
2) `sudo mysql < install/sql/001-mellivora.sql`
3) `sudo mysql < install/sql/002-countries.sql`
4) `sudo mysql < install/sql/004-initial_config.sql`

## Redis Configuration

This should be done on a separate droplet or machine that is within the same private network as MySQL and the various nodes. 


1) Install the following dependencies `redis`

2) Configure Redis to accept external connections. Edit `/etc/redis/redis.conf` and change/add the following
   - `bind localhost your_inet_addr`
   - `requirepass your_password`
3) Restart the Redis service so the changes take effect `sudo service redis-server restart`

## Node Configuration

1) Edit `php.ini` and change/add the following
   - `session.save_handler = redis`
   - `session.save_path = "tcp://IPADDRESS:PORT?auth=REDISPASSWORD"`

   REDIS password must be URL encoded

2) Copy `include/config/db.default.inc.php` to `include/config/db.inc.php` and configure. 
3) Copy `include/config/config.default.inc.php` to `include/config/config.inc.php` and configure. 
4) Restart Nginx and PHP-FPM

At this point, the individual node is ready. 

## S3 Bucket Storage

The platform allows for Amazon S3 Bucket storage or other compatible S3 storage platforms. To ensure files are consistent and synchronised between nodes, S3 bucket storage is to be configured under `include/config/config.inc.php` to allow each node to access challenge files. 

## Scaling

Scaling is possible through the use of a Load Balancer and spinning up multiple nodes with the same configuration. 

In Digital Ocean, this is possible by

1) Taking a snapshot of the original node and spinning up more nodes based on the saved snapshot
2) Assigning all nodes to the Load balancer under either `SSL Termination` mode or `SSL passthrough mode`. (Assuming you are enabling SSL)

## Final Steps

To Create an Admin User, 

1) Register your admin account on the website (and enable 2FA Authentication preferably)
2) Logout of your account (if you happen to be logged in)
3) sudo into `mysql` (on the MySQL droplet) and run the query `USE mellivora; UPDATE users SET class=100 WHERE id=1;`



