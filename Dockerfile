# Usa imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala dependências do sistema e extensões necessárias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copia os arquivos do projeto para o container
COPY . /var/www/html/

# Habilita o módulo rewrite do Apache
RUN a2enmod rewrite

# Exponha a porta padrão do Apache
EXPOSE 80
