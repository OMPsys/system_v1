FROM php:8.2-apache

# Install necessary packages
RUN apt-get update && apt-get install -y \
    autoconf \
    libpcre3-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libpng-dev \
    libicu-dev

RUN docker-php-ext-install mysqli gd intl

# Force a rebuild with no cache
RUN apt-get update && apt-get install -y autoconf

# Verify autoconf location (optional)
RUN which autoconf

# Habilitar mod_rewrite para o Apache
RUN a2enmod rewrite

# Copiar os arquivos locais para o diretório do contêiner
COPY . /var/www/html

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Adicionar permissões de escrita para os diretórios e arquivos necessários
RUN chmod -R 777 /var/www/html/files \
    && chmod 777 /var/www/html/index.php \
    && chmod 777 /var/www/html/app/Config/App.php \
    && chmod 777 /var/www/html/app/Config/Database.php

# Install PHP extensions (excluding potentially pre-installed ones)
RUN 
    WORKDIR /tmp/php_ext && \
    docker-php-ext-install mysqli openssl gd intl json mysqlnd xml \
    && docker-php-ext-configure zlib --with-zlib \
    && docker-php-ext-install zlib

RUN sed -i 's/;zlib.output_compression = Off/zlib.output_compression = Off/g' /usr/local/etc/php/php.ini-production

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

RUN a2enmod rewrite
