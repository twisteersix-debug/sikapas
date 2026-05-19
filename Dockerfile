FROM php:8.2-apache

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql

# Aktifkan mod_rewrite saja
RUN a2enmod rewrite

# Hapus konfigurasi yang konflik
RUN rm -f /etc/apache2/conf-available/simpas.conf \
    && rm -f /etc/apache2/conf-available/sipaten.conf \
    && rm -f /etc/apache2/conf-enabled/simpas.conf \
    && rm -f /etc/apache2/conf-enabled/sipaten.conf

# Izinkan .htaccess di semua folder
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy semua file aplikasi
COPY . /var/www/html/

# Buat folder upload & set permission
RUN mkdir -p /var/www/html/uploads/dokumen \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
