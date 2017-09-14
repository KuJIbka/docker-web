FROM php:7.1-fpm

ENV PHP_MODULE_IMAGICK_URL "https://pecl.php.net/get/imagick-3.4.3.tgz"
ENV PHP_MODULE_LIBSODIUM_URL "https://pecl.php.net/get/libsodium-2.0.4.tgz"

RUN apt-get update && apt-get install -y \
        zip \
        sudo \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libgmp-dev \
        libsodium-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mbstring mysqli pdo_mysql zip bcmath gmp \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -L -o /tmp/imagick.tar.gz "${PHP_MODULE_IMAGICK_URL}" \
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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini
WORKDIR /var/www

RUN curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
RUN apt-get install -y nodejs build-essential

EXPOSE 9001
CMD ["php-fpm"]
