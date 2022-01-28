FROM nginx:stable-alpine

ADD ./default.conf /etc/nginx/conf.d/default.conf
ADD ./uploads.conf /etc/nginx/conf.d/zz-uploads.conf

RUN mkdir -p /var/www/html

RUN mkdir -p /etc/nginx/certs/mkcert

ADD ./certs /etc/nginx/certs/mkcert
