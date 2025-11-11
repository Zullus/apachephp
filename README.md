# 🐘 PHP 8.4 Apache - Ambiente de Desenvolvimento

Dockerfile completo para ambiente de desenvolvimento PHP 8.4 com Apache, extensões essenciais e ferramentas modernas.

## 📋 Índice

- [Características](#-características)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação](#-instalação)
- [Uso Básico](#-uso-básico)
- [Extensões PHP](#-extensões-php)
- [SSL Opcional](#-ssl-opcional)
- [Docker Compose](#-docker-compose)
- [Configuração AWS](#-configuração-aws)
- [Xdebug](#-xdebug)
- [Troubleshooting](#-troubleshooting)

## ✨ Características

### 🔧 Stack Principal
- **PHP 8.4** com Apache 2.4
- **Composer** (gerenciador de dependências)
- **Git, Curl, Zip/Unzip**
- **AWS CLI v2** + AWS SDK para PHP

### 📦 Extensões PHP Instaladas

**Core:**
- bcmath, bz2, calendar, exif, gd, intl
- ldap, mysqli, opcache, sockets
- pdo_mysql, pdo_pgsql, pgsql
- soap, xsl, zip

**PECL:**
- redis, memcached, xdebug
- mongodb (driver MongoDB)
- amqp (cliente RabbitMQ)

### 🎯 Otimizado para Desenvolvimento
- PHP.ini configurado para debugging
- Todos os erros visíveis
- OPcache desabilitado (mudanças instantâneas)
- Xdebug pronto para usar
- SSL opcional via script

## 🔨 Pré-requisitos

- Docker 20.10+
- Docker Compose 2.0+ (opcional)
- 2GB de espaço em disco

## 🚀 Instalação

### 1. Clonar/Criar estrutura do projeto

```bash
mkdir meu-projeto && cd meu-projeto
```

### 2. Criar arquivos necessários

**Estrutura:**
```
meu-projeto/
├── Dockerfile
├── php.ini
├── docker-compose.yml (opcional)
└── src/
    └── index.php
```

### 3. Build da imagem

```bash
docker build -t php84-dev .
```

**Tempo estimado:** 5-10 minutos (primeira vez)

## 🎮 Uso Básico

### Executar container

```bash
docker run -d \
  --name php-dev \
  -p 80:80 \
  -p 443:443 \
  -v $(pwd)/src:/var/www/html \
  php84-dev
```

### Acessar aplicação

```
http://localhost
```

### Comandos úteis

```bash
# Ver logs do container
docker logs php-dev

# Acessar shell do container
docker exec -it php-dev bash

# Executar comandos PHP
docker exec php-dev php -v

# Instalar dependências com Composer
docker exec php-dev composer install

# Parar container
docker stop php-dev

# Remover container
docker rm php-dev
```

## 📦 Extensões PHP

### Verificar extensões instaladas

```bash
docker exec php-dev php -m
```

### Usar MongoDB

```php
<?php
// Usando driver nativo
$manager = new MongoDB\Driver\Manager("mongodb://mongo:27017");

// Ou instale a biblioteca oficial
// composer require mongodb/mongodb
$client = new MongoDB\Client("mongodb://mongo:27017");
$collection = $client->mydb->mycollection;
```

### Usar RabbitMQ

```php
<?php
$connection = new AMQPConnection([
    'host' => 'rabbitmq',
    'port' => 5672,
    'vhost' => '/',
    'login' => 'guest',
    'password' => 'guest'
]);

$connection->connect();
$channel = new AMQPChannel($connection);
$queue = new AMQPQueue($channel);
$queue->setName('minha-fila');
$queue->declareQueue();
```

### Usar Redis

```php
<?php
$redis = new Redis();
$redis->connect('redis', 6379);
$redis->set('chave', 'valor');
echo $redis->get('chave');
```

## 🔒 SSL Opcional

O SSL não vem habilitado por padrão. Para habilitar:

```bash
docker exec php-dev enable-ssl.sh
```

**Acesse via HTTPS:**
```
https://localhost
```

⚠️ **Nota:** Certificado auto-assinado gerará aviso no navegador (normal em desenvolvimento)

### Usar certificado customizado

```bash
# Copiar seus certificados
docker cp meu-cert.crt php-dev:/etc/ssl/certs/apache-selfsigned.crt
docker cp meu-cert.key php-dev:/etc/ssl/private/apache-selfsigned.key

# Habilitar SSL
docker exec php-dev enable-ssl.sh
```

## 🐳 Docker Compose

Crie um `docker-compose.yml` para orquestrar múltiplos serviços:

```yaml
version: '3.8'

services:
  php:
    build: .
    container_name: php-dev
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./src:/var/www/html
    environment:
      # Configurações AWS (opcional)
      - AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
      - AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
      - AWS_DEFAULT_REGION=us-east-1
      # Xdebug
      - XDEBUG_MODE=debug
    depends_on:
      - mysql
      - mongodb
      - redis
      - rabbitmq
    networks:
      - app-network

  mysql:
    image: mysql:8.0
    container_name: mysql-dev
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=app_db
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - app-network

  mongodb:
    image: mongo:latest
    container_name: mongo-dev
    ports:
      - "27017:27017"
    environment:
      - MONGO_INITDB_ROOT_USERNAME=root
      - MONGO_INITDB_ROOT_PASSWORD=root
    volumes:
      - mongo-data:/data/db
    networks:
      - app-network

  redis:
    image: redis:alpine
    container_name: redis-dev
    ports:
      - "6379:6379"
    networks:
      - app-network

  rabbitmq:
    image: rabbitmq:3-management
    container_name: rabbitmq-dev
    ports:
      - "5672:5672"   # AMQP
      - "15672:15672" # Management UI
    environment:
      - RABBITMQ_DEFAULT_USER=guest
      - RABBITMQ_DEFAULT_PASS=guest
    networks:
      - app-network

volumes:
  mysql-data:
  mongo-data:

networks:
  app-network:
    driver: bridge
```

### Usar com Docker Compose

```bash
# Subir todos os serviços
docker-compose up -d

# Ver logs
docker-compose logs -f php

# Parar todos os serviços
docker-compose down

# Reconstruir imagens
docker-compose build --no-cache
```

## ☁️ Configuração AWS

### Via variáveis de ambiente

```bash
docker run -d \
  -e AWS_ACCESS_KEY_ID=sua_chave \
  -e AWS_SECRET_ACCESS_KEY=sua_secret \
  -e AWS_DEFAULT_REGION=us-east-1 \
  -p 80:80 \
  php84-dev
```

### Via arquivo .env (com Docker Compose)

Crie `.env`:
```env
AWS_ACCESS_KEY_ID=sua_chave
AWS_SECRET_ACCESS_KEY=sua_secret
AWS_DEFAULT_REGION=us-east-1
```

### Usar AWS CLI

```bash
# Listar buckets S3
docker exec php-dev aws s3 ls

# Copiar arquivo para S3
docker exec php-dev aws s3 cp arquivo.txt s3://meu-bucket/
```

### Usar AWS SDK no PHP

```php
<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1',
    'credentials' => [
        'key'    => getenv('AWS_ACCESS_KEY_ID'),
        'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
    ]
]);

// Listar buckets
$result = $s3->listBuckets();
foreach ($result['Buckets'] as $bucket) {
    echo $bucket['Name'] . "\n";
}
```

## 🐛 Xdebug

### Configuração VS Code

Crie `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/src"
      }
    }
  ]
}
```

### Configuração PHPStorm

1. Settings → PHP → Servers
2. Criar novo server:
   - Name: `Docker`
   - Host: `localhost`
   - Port: `80`
   - Path mapping: `src → /var/www/html`

### Habilitar/Desabilitar Xdebug

```bash
# Dentro do container
php -dxdebug.mode=debug script.php

# Ou edite php.ini e recarregue
docker exec php-dev apache2ctl graceful
```

## 🔍 Troubleshooting

### Container não inicia

```bash
# Ver logs de erro
docker logs php-dev

# Verificar portas em uso
netstat -tuln | grep -E '80|443'
```

### Permissões de arquivo

```bash
# Ajustar permissões da pasta src
sudo chown -R $USER:$USER src/
chmod -R 755 src/
```

### Xdebug não conecta

1. Verificar firewall (porta 9003)
2. Confirmar `host.docker.internal` funciona
3. No Linux, use: `--add-host=host.docker.internal:host-gateway`

### Extensão não carrega

```bash
# Verificar se extensão está habilitada
docker exec php-dev php -m | grep nome_extensao

# Recompilar se necessário
docker exec php-dev pecl install nome_extensao
docker exec php-dev docker-php-ext-enable nome_extensao
```

### Performance lenta

```bash
# Desabilitar Xdebug quando não usar
docker exec php-dev bash -c "echo 'xdebug.mode=off' > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec php-dev apache2ctl graceful
```

## 📚 Recursos Adicionais

- [Documentação PHP 8.4](https://www.php.net/releases/8.4/en.php)
- [Apache Documentation](https://httpd.apache.org/docs/2.4/)
- [Composer](https://getcomposer.org/)
- [MongoDB PHP Driver](https://www.mongodb.com/docs/drivers/php/)
- [RabbitMQ AMQP](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
- [AWS SDK PHP](https://aws.amazon.com/sdk-for-php/)
- [Xdebug Documentation](https://xdebug.org/docs/)

## 📄 Licença

Este projeto é livre para uso pessoal e comercial.

## 🤝 Contribuições

Sugestões e melhorias são bem-vindas!

---

**⚠️ IMPORTANTE:** Este ambiente é otimizado para **DESENVOLVIMENTO**. Não use em produção sem ajustes de segurança adequados!