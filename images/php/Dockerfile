FROM php:7.1-fpm

ENV PHP_MODULE_IMAGICK_URL "https://pecl.php.net/get/imagick-3.4.3.tgz"
ENV LIBSODIUM_LIB_URL "https://download.libsodium.org/libsodium/releases/libsodium-1.0.13.tar.gz"
ENV PHP_MODULE_LIBSODIUM_URL "https://pecl.php.net/get/libsodium-2.0.4.tgz"
ENV REDIS_URL "https://github.com/phpredis/phpredis/archive/3.1.3.tar.gz"
ENV V8_VERSION 6.0.318
ENV V8JS_VERSION=1.4.1
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
        libssl-dev \
#        libsodium-dev \
        libmagickwand-dev \
        libmagickcore-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mbstring pdo_mysql zip bcmath gmp json xml dom fileinfo \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# RUN git config --global http.sslverify false

# RUN apt-get install --reinstall ca-certificates \
#    && mkdir /usr/local/share/ca-certificates/cacert.org \
#    && wget -P /usr/local/share/ca-certificates/cacert.org http://www.cacert.org/certs/root.crt http://www.cacert.org/certs/class3.crt \
#    && update-ca-certificates \
#    && git config --global https.sslCAinfo /etc/ssl/certs/ca-certificates.crt \
#    && git config --global http.sslCAinfo /etc/ssl/certs/ca-certificates.crt

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN git clone https://github.com/tideways/php-profiler-extension.git \
 && cd php-profiler-extension \
 && phpize \
 &&  ./configure \
 && make \
 && make install

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

    && docker-php-ext-install libsodium imagick tideways

#RUN curl -L -o /tmp/redis.tar.gz "${REDIS_URL}" \
#    && tar xfz /tmp/redis.tar.gz \
#    && rm -r /tmp/redis.tar.gz \
#    && mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
#    && docker-php-ext-install redis

RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini
WORKDIR /var/www

RUN curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
RUN apt-get install -y nodejs build-essential libglib2.0-dev

RUN cd /tmp \
    # Install depot_tools first (needed for source checkout)
    && git clone https://chromium.googlesource.com/chromium/tools/depot_tools.git \
    && export PATH=`pwd`/depot_tools:"$PATH" \

    # Download v8
    && fetch v8 \
    && cd v8 \

    # (optional) If you'd like to build a certain version:
    && git checkout ${V8_VERSION} \
    && gclient sync \

    # Setup GN
    && tools/dev/v8gen.py -vv x64.release -- is_component_build=true \

    # Build
    && ninja -C out.gn/x64.release/ \

    # Install to /opt/v8/
    && mkdir -p /opt/v8/lib \
    && mkdir -p /opt/v8/include \
    && cp out.gn/x64.release/lib*.so out.gn/x64.release/*_blob.bin \
       out.gn/x64.release/icudtl.dat /opt/v8/lib/ \
    &&  cp -R include/* /opt/v8/include/

RUN cd /usr/local/src \
    && curl -L https://github.com/phpv8/v8js/archive/${V8JS_VERSION}.tar.gz -o v8js-${V8JS_VERSION}.tar.gz \
    && tar xzf v8js-${V8JS_VERSION}.tar.gz \
    && cd v8js-${V8JS_VERSION} \
    && phpize \
    && ./configure --with-v8js=/opt/v8 \
    && export NO_INTERACTION=1 \
    && make all test install

EXPOSE 9001
CMD ["php-fpm"]
