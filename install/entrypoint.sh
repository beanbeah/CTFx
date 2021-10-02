/etc/init.d/$(ls /etc/init.d | grep php) start
nginx

while true; do sleep 1000; done