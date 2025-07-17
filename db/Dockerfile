FROM php:8.2-apache

# Instalar extensiones necesarias: PDO, MySQLi
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache (opcional)
RUN a2enmod rewrite
