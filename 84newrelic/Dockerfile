FROM php:8.4-apache

# Definir variáveis de ambiente
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

# Variáveis de ambiente do New Relic
ENV NEW_RELIC_LICENSE_KEY="" \
    NEW_RELIC_APP_NAME="Minha Aplicacao PHP" \
    NEW_RELIC_ENABLED=true

# Variável para controlar Xdebug (desligado por padrão para não conflitar com New Relic)
ENV XDEBUG_ENABLED=false

# Atualizar sistema e instalar dependências
RUN apt-get update && apt-get install -y \
    # Ferramentas básicas
    curl \
    git \
    zip \
    unzip \
    # Bibliotecas para extensões PHP
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxslt1-dev \
    libbz2-dev \
    libldap2-dev \
    libmemcached-dev \
    libicu-dev \
    zlib1g-dev \
    # Dependências para SQL Server
    gnupg2 \
    apt-transport-https \
    # Dependências para RabbitMQ
    librabbitmq-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar extensão GD com suporte a JPEG e FreeType
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instalar extensões PHP padrão
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    bz2 \
    calendar \
    exif \
    gd \
    intl \
    ldap \
    mysqli \
    opcache \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    soap \
    xsl \
    zip \
    sockets

# Instalar dependências para MongoDB
RUN apt-get update && apt-get install -y \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PECL (sem xdebug por enquanto)
RUN pecl install redis memcached mongodb amqp \
    && docker-php-ext-enable redis memcached mongodb amqp

# Instalar Xdebug mas NÃO habilitar automaticamente
RUN pecl install xdebug

# ========================================
# INSTALAÇÃO DO NEW RELIC
# ========================================

# Instalar o agente New Relic
RUN export NEW_RELIC_AGENT_VERSION=$(curl -s https://download.newrelic.com/php_agent/release/ | grep -o '[0-9]\+\(\.[0-9]\+\)\{3\}' | head -n1) && \
    curl -L "https://download.newrelic.com/php_agent/release/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux.tar.gz" | tar -C /tmp -zx && \
    export NR_INSTALL_USE_CP_NOT_LN=1 && \
    export NR_INSTALL_SILENT=1 && \
    /tmp/newrelic-php5-*/newrelic-install install && \
    rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
    mkdir -p /var/log/newrelic && \
    chmod -R 777 /var/log/newrelic

# Configurar o New Relic via arquivo de configuração
RUN echo "[newrelic]" > /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "extension=newrelic.so" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.license = \"\${NEW_RELIC_LICENSE_KEY}\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.appname = \"\${NEW_RELIC_APP_NAME}\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.enabled = \${NEW_RELIC_ENABLED}" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.logfile = \"/var/log/newrelic/php_agent.log\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.loglevel = \"info\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.daemon.logfile = \"/var/log/newrelic/newrelic-daemon.log\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.daemon.loglevel = \"info\"" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.daemon.app_connect_timeout = 15s" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.daemon.start_timeout = 5s" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "; Captura de erros e exceções" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.error_collector.enabled = true" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.error_collector.record_database_errors = true" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "; Distributed tracing" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.distributed_tracing_enabled = true" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "; Monitoramento de transações" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.transaction_tracer.enabled = true" >> /usr/local/etc/php/conf.d/newrelic.ini && \
    echo "newrelic.transaction_tracer.detail = 1" >> /usr/local/etc/php/conf.d/newrelic.ini

# ========================================
# FIM DA INSTALAÇÃO DO NEW RELIC
# ========================================

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar AWS CLI
RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install \
    && rm -rf aws awscliv2.zip

# Instalar AWS SDK para PHP via Composer (globalmente)
RUN composer global require aws/aws-sdk-php \
    && composer clear-cache

# Habilitar módulos Apache necessários
RUN a2enmod rewrite headers

# Configurar VirtualHost padrão para desenvolvimento
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    \n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Criar diretório para logs do Xdebug
RUN mkdir -p /tmp/xdebug && chown www-data:www-data /tmp/xdebug

# Criar script para habilitar/desabilitar Xdebug
COPY <<'EOF' /usr/local/bin/toggle-xdebug.sh
#!/bin/bash
set -e

XDEBUG_INI="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

if [ "$1" = "on" ]; then
    echo "🐛 Habilitando Xdebug..."
    docker-php-ext-enable xdebug
    
    # Configurar Xdebug
    cat > $XDEBUG_INI <<'XDEBUG_CONFIG'
zend_extension=xdebug.so
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.log=/tmp/xdebug/xdebug.log
XDEBUG_CONFIG
    
    echo "⚠️  ATENÇÃO: Xdebug habilitado - New Relic NÃO capturará erros!"
    echo "⚠️  Para produção, use: toggle-xdebug.sh off"
    
elif [ "$1" = "off" ]; then
    echo "🔧 Desabilitando Xdebug..."
    if [ -f "$XDEBUG_INI" ]; then
        rm -f "$XDEBUG_INI"
    fi
    echo "✅ Xdebug desabilitado - New Relic funcionando normalmente"
    
else
    echo "Uso: toggle-xdebug.sh [on|off]"
    echo ""
    echo "  on  - Habilita Xdebug (desenvolvimento)"
    echo "  off - Desabilita Xdebug (produção/New Relic)"
    exit 1
fi

# Recarregar Apache
if [ -f /var/run/apache2/apache2.pid ]; then
    kill -USR1 $(cat /var/run/apache2/apache2.pid) 2>/dev/null || true
fi

echo "✅ Pronto! Reinicie o Apache se necessário."
EOF

RUN chmod +x /usr/local/bin/toggle-xdebug.sh

# Criar script para habilitar SSL
COPY <<'EOF' /usr/local/bin/enable-ssl.sh
#!/bin/bash
set -e

echo "🔒 Habilitando SSL no Apache..."

# Habilitar módulo SSL
a2enmod ssl

# Criar certificado auto-assinado se não existir
if [ ! -f /etc/ssl/private/apache-selfsigned.key ]; then
    echo "📜 Gerando certificado SSL auto-assinado..."
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/private/apache-selfsigned.key \
        -out /etc/ssl/certs/apache-selfsigned.crt \
        -subj "/C=BR/ST=SaoPaulo/L=SaoPaulo/O=Development/CN=localhost"
fi

# Criar configuração SSL
cat > /etc/apache2/sites-available/default-ssl.conf <<'SSLCONF'
<IfModule mod_ssl.c>
    <VirtualHost _default_:443>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html

        SSLEngine on
        SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt
        SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key

        <FilesMatch "\.(cgi|shtml|phtml|php)$">
            SSLOptions +StdEnvVars
        </FilesMatch>

        <Directory /var/www/html>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/ssl_error.log
        CustomLog ${APACHE_LOG_DIR}/ssl_access.log combined
    </VirtualHost>
</IfModule>
SSLCONF

# Habilitar site SSL
a2ensite default-ssl

# Recarregar Apache
service apache2 reload

echo "✅ SSL habilitado! Acesse via https://localhost"
echo "⚠️  Certificado auto-assinado - você verá avisos de segurança no navegador"
EOF

RUN chmod +x /usr/local/bin/enable-ssl.sh

# Definir permissões
RUN chown -R www-data:www-data /var/www/html

# Criar um script de entrada personalizado
COPY <<'EOF' /usr/local/bin/docker-entrypoint.sh
#!/bin/bash
set -e

echo "🚀 Iniciando ambiente de desenvolvimento PHP 8.4"
echo "📦 Extensões PHP instaladas:"
php -m | grep -v "^\[" | sort

echo ""
echo "📊 Configurações importantes:"
echo "   - Display Errors: $(php -r 'echo ini_get("display_errors") ? "ON" : "OFF";')"
echo "   - Memory Limit: $(php -r 'echo ini_get("memory_limit");')"
echo "   - Upload Max: $(php -r 'echo ini_get("upload_max_filesize");')"
echo "   - MongoDB: $(php -r 'echo extension_loaded("mongodb") ? "Instalado" : "Não instalado";')"
echo "   - AMQP (RabbitMQ): $(php -r 'echo extension_loaded("amqp") ? "Instalado" : "Não instalado";')"
echo ""

# Gerenciar Xdebug com base na variável de ambiente
if [ "$XDEBUG_ENABLED" = "true" ]; then
    echo "🐛 Xdebug: Habilitando (modo desenvolvimento)..."
    /usr/local/bin/toggle-xdebug.sh on > /dev/null 2>&1
    echo "   - Xdebug: ✅ Habilitado"
    echo "   ⚠️  New Relic NÃO capturará erros com Xdebug ativo!"
else
    echo "🐛 Xdebug: Desabilitado (modo produção)"
    /usr/local/bin/toggle-xdebug.sh off > /dev/null 2>&1
    echo "   - Xdebug: ❌ Desabilitado"
fi

echo ""
echo "🔧 Ferramentas instaladas:"
echo "   - Composer: $(composer --version | head -n1)"
echo "   - AWS CLI: $(aws --version)"
echo ""

# Verificar configuração do New Relic
if [ -n "$NEW_RELIC_LICENSE_KEY" ] && [ "$NEW_RELIC_ENABLED" = "true" ]; then
    echo "📡 New Relic configurado:"
    echo "   - App Name: $NEW_RELIC_APP_NAME"
    echo "   - Status: ✅ Ativo"
    echo "   - Logs: /var/log/newrelic/"
    if [ "$XDEBUG_ENABLED" = "true" ]; then
        echo "   ⚠️  AVISO: Xdebug habilitado - erros NÃO serão capturados!"
    fi
else
    echo "📡 New Relic: ⚠️  Não configurado (defina NEW_RELIC_LICENSE_KEY)"
fi

echo ""
echo "🔧 Comandos úteis:"
echo "   - Habilitar SSL: docker exec <container> enable-ssl.sh"
echo "   - Habilitar Xdebug: docker exec <container> toggle-xdebug.sh on"
echo "   - Desabilitar Xdebug: docker exec <container> toggle-xdebug.sh off"
echo ""

# Executar comando original do Apache
exec apache2-foreground
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expor portas
EXPOSE 80 443

# Usar script de entrada personalizado
CMD ["/usr/local/bin/docker-entrypoint.sh"]