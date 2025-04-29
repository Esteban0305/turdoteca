FROM php:8.2-apache

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip

# Habilita mod_rewrite si lo necesitas (opcional pero común)
RUN a2enmod rewrite

# Copia los archivos del proyecto
COPY . /var/www/html/

# Da permisos adecuados (si tu app escribe en archivos)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expone el puerto 80 (por default de Apache)
EXPOSE 80
