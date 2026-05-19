FROM php:8.2-apache

# Fix MPM conflict — nonaktifkan mpm_event, aktifkan mpm_prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql

# Izinkan .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy semua file aplikasi ke web root
COPY . /var/www/html/

# Buat folder upload & set permission
RUN mkdir -p /var/www/html/uploads/dokumen \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
