FROM php:8.4-apache

# Definir timezone
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libffi-dev \
    libzip-dev \
    libbz2-dev \
    libgmp-dev \
    libgd-dev \
    libicu-dev \
    libldap2-dev \
    libsasl2-dev \
    libssl-dev \
    libmcrypt-dev \
    libmagickwand-dev \
    libtidy-dev \
    libxslt1-dev \
    libonig-dev \
    libssh2-1-dev \
    unixodbc-dev \
    libiodbc2-dev \
    freetds-dev \
    postgresql-client \
    git \
    curl \
    wget \
    zip \
    unzip \
    sendmail \
    && rm -rf /var/lib/apt/lists/*

# Compilar e instalar extensões PHP
RUN docker-php-ext-configure gd \
    --enable-gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm

RUN docker-php-ext-configure pgsql \
    --with-pgsql=/usr/lib/postgresql

RUN docker-php-ext-configure ldap \
    --with-ldap=/usr \
    --with-ldap-sasl=/usr

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    bz2 \
    calendar \
    ctype \
    curl \
    dba \
    dom \
    exif \
    fileinfo \
    filter \
    ftp \
    gd \
    gettext \
    gmp \
    iconv \
    intl \
    json \
    ldap \
    mbstring \
    mysql \
    mysqli \
    odbc \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_odbc \
    pgsql \
    posix \
    shmop \
    simplexml \
    soap \
    sockets \
    sqlite3 \
    pdo_sqlite \
    sysvmsg \
    sysvsem \
    sysvshm \
    tidy \
    tokenizer \
    xml \
    xmlreader \
    xmlwriter \
    xsl \
    zip \
    zlib

# Instalar extensões via pecl
RUN pecl install -f igbinary && docker-php-ext-enable igbinary
RUN pecl install -f apcu && docker-php-ext-enable apcu
RUN pecl install -f imagick && docker-php-ext-enable imagick
RUN pecl install -f mcrypt && docker-php-ext-enable mcrypt
RUN pecl install -f mongodb && docker-php-ext-enable mongodb
RUN pecl install -f redis && docker-php-ext-enable redis

# Instalar pdo_sqlsrv e sqlsrv
RUN apt-get update && \
    curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - && \
    curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools && \
    pecl install pdo_sqlsrv sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv sqlsrv && \
    rm -rf /var/lib/apt/lists/*

# Instalar OAuth 2.0
RUN pecl install oauth && docker-php-ext-enable oauth

# Instalar extensões adicionais
RUN pecl install json_post && docker-php-ext-enable json_post
RUN pecl install jsonpath && docker-php-ext-enable jsonpath
RUN pecl install phpiredis && docker-php-ext-enable phpiredis
RUN pecl install ssh2 && docker-php-ext-enable ssh2

# Configurar PHP.ini
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=32" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=5000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Copiar configuração customizada
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Habilitar módulos Apache necessários
RUN a2enmod rewrite && \
    a2enmod ssl && \
    a2enmod proxy && \
    a2enmod proxy_fcgi && \
    a2enmod headers && \
    a2enmod deflate

# Criar diretórios de logs
RUN mkdir -p /var/log/apache2 && \
    chmod -R 777 /var/log/apache2 && \
    mkdir -p /tmp/php_sessions && \
    chmod -R 777 /tmp/php_sessions

# Definir diretório de trabalho
WORKDIR /var/www/html

# Expor portas
EXPOSE 80 443

# Comando inicial
CMD ["apache2-foreground"]