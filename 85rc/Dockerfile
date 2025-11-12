FROM php:8.5-rc-apache

# ⚠️ ATENÇÃO: PHP 8.5 RC - USE APENAS PARA TESTES
# NÃO usar em produção ou desenvolvimento principal!

# Definir variáveis de ambiente
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_VERSION_LABEL="8.5-RC"

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
    libssl-dev \
    librabbitmq-dev \
    # Dependências adicionais importantes
    libonig-dev \
    libxml2-dev \
    libsodium-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Configurar extensão GD com suporte a JPEG e FreeType
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Configurar extensão LDAP
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/

# Instalar extensões PHP uma por uma para identificar problemas
RUN echo "==> Instalando bcmath..." && docker-php-ext-install bcmath
RUN echo "==> Instalando bz2..." && docker-php-ext-install bz2
RUN echo "==> Instalando calendar..." && docker-php-ext-install calendar
RUN echo "==> Instalando exif..." && docker-php-ext-install exif
RUN echo "==> Instalando mysqli..." && docker-php-ext-install mysqli
#RUN echo "==> Instalando opcache..." && docker-php-ext-install opcache
RUN echo "==> Instalando pdo_mysql..." && docker-php-ext-install pdo_mysql
RUN echo "==> Instalando pdo_pgsql..." && docker-php-ext-install pdo_pgsql
RUN echo "==> Instalando pgsql..." && docker-php-ext-install pgsql
RUN echo "==> Instalando sockets..." && docker-php-ext-install sockets
RUN echo "==> Instalando zip..." && docker-php-ext-install zip
RUN echo "==> Extensões básicas instaladas!"

# Extensões que precisam de configuração
RUN echo "==> Instalando GD..." && docker-php-ext-install gd
RUN echo "==> Instalando INTL..." && docker-php-ext-install intl
RUN echo "==> Instalando LDAP..." && docker-php-ext-install ldap
RUN echo "==> Instalando SOAP..." && docker-php-ext-install soap
RUN echo "==> Instalando XSL..." && docker-php-ext-install xsl
RUN echo "==> Todas as extensões core instaladas!"

# Instalar extensões PECL (algumas podem não estar disponíveis ainda para 8.5)
# Usar --force para tentar instalar mesmo com avisos de compatibilidade
RUN pecl config-set php_ini "$PHP_INI_DIR/php.ini" 
RUN pecl install --force redis 
RUN pecl install --force memcached 
RUN pecl install --force mongodb 
#RUN pecl install --force amqp 
RUN docker-php-ext-enable redis memcached mongodb

# Xdebug pode não estar disponível ainda - tentar instalar
RUN pecl install --force xdebug || echo "⚠️  Xdebug não disponível para PHP 8.5 RC"
RUN docker-php-ext-enable xdebug || echo "⚠️  Xdebug não habilitado"

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

# Copiar php.ini de teste (otimizado para debug e novos recursos)
COPY <<'EOF' $PHP_INI_DIR/php.ini
[PHP]
; ⚠️ Configuração para TESTES do PHP 8.5 RC

;;;;;;;;;;;;;;;;;;;
; Exibição de Erros - MÁXIMO para testar novos recursos
;;;;;;;;;;;;;;;;;;;
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /var/log/php_errors.log
html_errors = On

;;;;;;;;;;;;;;;;;;;
; Avisos de Deprecated - CRÍTICO para PHP 8.5
;;;;;;;;;;;;;;;;;;;
; Mostrar TODOS os avisos de recursos deprecados
error_reporting = E_ALL | E_DEPRECATED | E_STRICT

;;;;;;;;;;;;;;;;;;;
; Limites generosos para testes
;;;;;;;;;;;;;;;;;;;
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
upload_max_filesize = 128M
post_max_size = 128M
max_file_uploads = 20

;;;;;;;;;;;;;;;;;;;
; Xdebug 3.x (se disponível)
;;;;;;;;;;;;;;;;;;;
xdebug.mode = debug,develop,coverage
xdebug.start_with_request = yes
xdebug.client_host = host.docker.internal
xdebug.client_port = 9003
xdebug.idekey = VSCODE
xdebug.var_display_max_depth = 10
xdebug.var_display_max_children = 256

;;;;;;;;;;;;;;;;;;;
; OPcache - Desabilitado para ver mudanças
;;;;;;;;;;;;;;;;;;;
opcache.enable = 0
opcache.enable_cli = 0

;;;;;;;;;;;;;;;;;;;
; JIT (novo no PHP 8.5) - ATIVADO para testes
;;;;;;;;;;;;;;;;;;;
opcache.jit = tracing
opcache.jit_buffer_size = 100M

;;;;;;;;;;;;;;;;;;;
; Configurações Gerais
;;;;;;;;;;;;;;;;;;;
default_charset = "UTF-8"
date.timezone = America/Sao_Paulo
max_input_vars = 10000
max_input_nesting_level = 128

;;;;;;;;;;;;;;;;;;;
; Sessões
;;;;;;;;;;;;;;;;;;;
session.save_handler = files
session.save_path = "/tmp"
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_samesite = Lax

;;;;;;;;;;;;;;;;;;;
; Segurança relaxada para testes
;;;;;;;;;;;;;;;;;;;
short_open_tag = On
expose_php = On
allow_url_fopen = On
allow_url_include = Off

;;;;;;;;;;;;;;;;;;;
; Extensões
;;;;;;;;;;;;;;;;;;;
[bcmath]
bcmath.scale = 2

[soap]
soap.wsdl_cache_enabled = 0
soap.wsdl_cache_dir = "/tmp"

[intl]
intl.default_locale = pt_BR
intl.error_level = E_WARNING

[mysqli]
mysqli.allow_local_infile = On

[mbstring]
mbstring.language = Neutral
mbstring.internal_encoding = UTF-8
mbstring.http_output = UTF-8
mbstring.encoding_translation = On
EOF

# Configurar VirtualHost padrão
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

# Criar diretórios
RUN mkdir -p /tmp/xdebug && chown www-data:www-data /tmp/xdebug

# Script para habilitar SSL
COPY <<'EOF' /usr/local/bin/enable-ssl.sh
#!/bin/bash
set -e

echo "🔒 Habilitando SSL no Apache..."

a2enmod ssl

if [ ! -f /etc/ssl/private/apache-selfsigned.key ]; then
    echo "📜 Gerando certificado SSL auto-assinado..."
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/private/apache-selfsigned.key \
        -out /etc/ssl/certs/apache-selfsigned.crt \
        -subj "/C=BR/ST=SaoPaulo/L=SaoPaulo/O=Testing/CN=localhost"
fi

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

a2ensite default-ssl
service apache2 reload

echo "✅ SSL habilitado!"
EOF

RUN chmod +x /usr/local/bin/enable-ssl.sh

# Script de relatório de compatibilidade
COPY <<'EOF' /usr/local/bin/php85-report.sh
#!/bin/bash

echo "================================================"
echo "🧪 PHP 8.5 RC - RELATÓRIO DE COMPATIBILIDADE"
echo "================================================"
echo ""
echo "📌 Versão: $(php -v | head -n1)"
echo ""
echo "⚠️  ATENÇÃO: Esta é uma versão RC (Release Candidate)"
echo "   Use APENAS para testes de compatibilidade!"
echo ""
echo "📦 Extensões Instaladas:"
php -m | grep -v "^\[" | sort | sed 's/^/   ✓ /'
echo ""
echo "🔍 Extensões PECL com possíveis problemas:"
php -m 2>&1 | grep -i "warning\|error\|deprecated" || echo "   ✓ Nenhum aviso detectado"
echo ""
echo "🧰 Ferramentas:"
echo "   - Composer: $(composer --version 2>&1 | head -n1)"
echo "   - AWS CLI: $(aws --version 2>&1)"
echo ""
echo "📝 Para testar seu código:"
echo "   1. Copie seus arquivos para ./src/"
echo "   2. Execute: docker exec php85-test php seu-script.php"
echo "   3. Verifique avisos de deprecated no log"
echo ""
echo "🐛 Logs de erro: /var/log/php_errors.log"
echo "================================================"
EOF

RUN chmod +x /usr/local/bin/php85-report.sh

# Criar página de teste padrão
RUN echo '<?php\n\
echo "<h1>🧪 PHP " . PHP_VERSION . " - Ambiente de Teste</h1>";\n\
echo "<p><strong>⚠️ ATENÇÃO:</strong> Esta é uma versão RC (Release Candidate)</p>";\n\
echo "<h2>Informações:</h2>";\n\
phpinfo();\n\
?>' > /var/www/html/index.php

# Definir permissões
RUN chown -R www-data:www-data /var/www/html

# Script de entrada personalizado
COPY <<'EOF' /usr/local/bin/docker-entrypoint.sh
#!/bin/bash
set -e

echo ""
echo "⚠️ ================================================ ⚠️"
echo "   PHP 8.5 RC - AMBIENTE DE TESTES"
echo "   NÃO USE EM PRODUÇÃO OU DESENVOLVIMENTO PRINCIPAL!"
echo "⚠️ ================================================ ⚠️"
echo ""

# Executar relatório
/usr/local/bin/php85-report.sh

echo ""
echo "🚀 Iniciando Apache..."
echo "   Acesse: http://localhost (ou porta mapeada)"
echo "   Relatório: docker exec php85-test php85-report.sh"
echo ""

exec apache2-foreground
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expor portas
EXPOSE 80 443

# Usar script de entrada personalizado
CMD ["/usr/local/bin/docker-entrypoint.sh"]