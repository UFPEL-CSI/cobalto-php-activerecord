#!/bin/bash
apk --update add openssh-client make grep autoconf gcc libc-dev zlib-dev;
apk add memcached
apk add php5-mysql;
apk add php5-pdo_mysql
apk add php5-mysqli;
apk add php5-pgsql;
apk add php5-pdo_pgsql
apk add composer;
# cd /tmp \
#     && curl -o php-memcache.tgz http://pecl.php.net/get/memcache-3.0.8.tgz \
#     && tar -xzvf php-memcache.tgz \
#     && cd memcache-3.0.8 \
#     && curl -o memcache-faulty-inline.patch http://git.alpinelinux.org/cgit/aports/plain/main/php5-memcache/memcache-faulty-inline.patch?h=3.4-stable \
#     && patch -p1 -i memcache-faulty-inline.patch \
#     && phpize \
#     && ./configure --prefix=/usr \
#     && make INSTALL_ROOT=/ install \
#     && install -d ./etc/php/conf.d; # \
    #&& echo "extension=memcache.so" > /usr/local/etc/php/conf.d/docker-php-ext-memcache.ini;
    #  \
    # && echo "extension=php_pdo_pgsql.dll" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini \
    # && echo "extension=php_pgsql.dll" > /usr/local/etc/php/conf.d/docker-php-ext-pgsql.ini \
    # && echo "extension=php_pdo_mysql.dll" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini \
    # && echo "extension=php_mysqli.dll" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini;
composer update;
CFLAGS="-fgnu89-inline" pecl install memcache-3.0.7;
php-fpm;
