FROM php:7.4.12-fpm-alpine

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

RUN mkdir -p /var/www/html

# check for supported extension
# https://github.com/mlocati/docker-php-extension-installer#supported-php-extensions
RUN install-php-extensions gd redis imagick gmp @composer pdo_mysql zip xdebug

ADD ./www.conf /usr/local/etc/php-fpm.d/www.conf
ADD ./php.ini /usr/local/etc/php/conf.d/zz-uploads.ini
ADD ./xdebug.ini /usr/local/etc/php/conf.d/yy-xdebug.ini

RUN rm -rf /tmp/* /var/cache/apk/*

RUN chown laravel:laravel /var/www/html
