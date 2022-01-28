```bash
    # Timezone set on server
    TZ=UTC
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

    # update apt package
    sudo apt-get update
    sudo apt-get install -y gnupg gosu curl software-properties-common ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 imagemagick mcrypt libmcrypt4 libpcre3-dev php-imagick php-memcached php-redis php-dev libgmp-dev

    sudo mkdir -p ~/.gnupg
    sudo chmod 600 ~/.gnupg
    sudo echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf
    sudo apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C
    sudo apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C
    sudo echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ppa_ondrej_php.list
    sudo add-apt-repository ppa:certbot/certbot -y
    sudo apt-get update

    sudo apt-get install python-certbot-nginx
    sudo certbot --nginx

    # install php7.4
    sudo apt-get install -y php7.4-cli php7.4-dev \
       php7.4-pgsql php7.4-sqlite3 php7.4-gd \
       php7.4-curl php7.4-memcached \
       php7.4-imap php7.4-mysql php7.4-mbstring \
       php7.4-xml php7.4-zip php7.4-bcmath php7.4-soap \
       php7.4-intl php7.4-readline php7.4-pcov \
       php7.4-msgpack php7.4-igbinary php7.4-ldap php7.4-gmp php7.4-redis

    # install composer/nodejs/mysql-client
    sudo php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -sL https://deb.nodesource.com/setup_15.x | bash - \
    && apt-get install -y nodejs \
    && apt-get install -y mysql-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

    # install nginx
    sudo apt-get install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages nginx


    # redis install
    sudo apt-get install -y redis-server
    sudo service redis-server start
```
