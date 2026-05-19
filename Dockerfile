FROM php:8.2-apache

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Disable semua MPM dulu, lalu enable prefork saja
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true  
RUN a2dismod mpm_prefork || true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

# Konfigurasi Apache langsung (tanpa .htaccess)
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copy aplikasi
COPY . /var/www/html/

# Set permission
RUN mkdir -p /var/www/html/uploads/dokumen \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Hapus .htaccess agar tidak konflik
RUN rm -f /var/www/html/.htaccess

EXPOSE 80
CMD ["apache2-foreground"]
