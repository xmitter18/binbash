FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Instalar Composer (se copia desde la imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar PHPUnit global (opcional)
RUN composer global require phpunit/phpunit

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar directorio de trabajo
WORKDIR /var/www/html
