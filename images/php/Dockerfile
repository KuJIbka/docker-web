FROM php:7.1-fpm

ENV PHP_MODULE_IMAGICK_URL "https://pecl.php.net/get/imagick-3.4.3.tgz"
ENV LIBSODIUM_LIB_URL "https://download.libsodium.org/libsodium/releases/libsodium-1.0.13.tar.gz"
ENV PHP_MODULE_LIBSODIUM_URL "https://pecl.php.net/get/libsodium-2.0.4.tgz"
ENV REDIS_URL "https://github.com/phpredis/phpredis/archive/3.1.3.tar.gz"
ENV V8_VERSION 6.3.239
ENV NO_INTERACTION 1


RUN apt-get update && apt-get install -y \
        zip \
        nano \
        sudo \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libgmp-dev \
#        libsodium-dev \
        libmagickwand-dev \
        libmagickcore-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mbstring pdo_mysql zip bcmath gmp json xml dom fileinfo \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN curl -k -L -o /tmp/libsodium_lib.tar.gz "${LIBSODIUM_LIB_URL}" \
 && mkdir -p /usr/src/libsodium \
 && tar -C /usr/src/libsodium -zxvf /tmp/libsodium_lib.tar.gz --strip 1 \
 && rm /tmp/libsodium_lib.tar.gz \
 && cd /usr/src/libsodium \
 && ./configure \
 && make \
 && make install

RUN curl -k -L -o /tmp/imagick.tar.gz "${PHP_MODULE_IMAGICK_URL}" \
    && mkdir -p /usr/src/php/ext/imagick \
    && tar -C /usr/src/php/ext/imagick -zxvf /tmp/imagick.tar.gz --strip 1 \
    && rm /tmp/imagick.tar.gz \
    && docker-php-ext-configure imagick --with-imagick=/opt/local \

    && curl -L -o /tmp/libsodium.tar.gz "${PHP_MODULE_LIBSODIUM_URL}" \
    && mkdir -p /usr/src/php/ext/libsodium \
    && tar -C /usr/src/php/ext/libsodium -zxvf /tmp/libsodium.tar.gz --strip 1 \
    && rm /tmp/libsodium.tar.gz \
    && docker-php-ext-configure libsodium \

    && docker-php-ext-install libsodium imagick

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        python2.7 xz-utils && \
    ln -s /usr/bin/python2.7 /usr/bin/python && \
    \
    git clone https://chromium.googlesource.com/chromium/tools/depot_tools.git /tmp/depot_tools && \
    export PATH="$PATH:/tmp/depot_tools" && \
    \
    cd /usr/local/src && fetch v8 && cd v8 && \
    git checkout "${V8_VERSION}" && gclient sync && \
    tools/dev/v8gen.py -vv x64.release -- is_component_build=true && \
    ninja -C out.gn/x64.release/ && \
    \
    mkdir -p /usr/local/lib && \
    cp out.gn/x64.release/lib*.so out.gn/x64.release/*_blob.bin out.gn/x64.release/icudtl.dat /usr/local/lib && \
    cp -R include/* /usr/local/include/ && \
    \
    git clone https://github.com/phpv8/v8js.git /usr/local/src/v8js && \
    cd /usr/local/src/v8js && phpize && ./configure --with-v8js=/usr/local && \
    export NO_INTERACTION=1 && make all -j4 && make test install && \
    \
    cd /tmp && \
    rm -rf /tmp/depot_tools /usr/local/src/v8 /usr/local/src/v8js && \
    apt-get remove -y python2.7 xz-utils && \
    apt-get autoremove -y && \
    apt-get clean

#RUN curl -L -o /tmp/redis.tar.gz "${REDIS_URL}" \
#    && tar xfz /tmp/redis.tar.gz \
#    && rm -r /tmp/redis.tar.gz \
#    && mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
#    && docker-php-ext-install redis

RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini
WORKDIR /var/www

RUN curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
RUN apt-get install -y nodejs build-essential

EXPOSE 9001
CMD ["php-fpm"]
