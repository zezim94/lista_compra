# Usa imagem oficial PHP com Apache
FROM php:8.2-apache

# Instala extensões PHP necessárias (ajuste conforme seu projeto)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copia os arquivos do projeto para o container
COPY . /var/www/html/

# Habilita rewrite (útil para .htaccess, se usar)
RUN a2enmod rewrite

# Exponha a porta padrão do Apache
EXPOSE 80
