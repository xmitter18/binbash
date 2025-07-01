FROM php:8.2-apache

# Instalar el driver PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar Apache mod_rewrite (opcional)
RUN a2enmod rewrite
