FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy aplikasi ke web root
COPY . /var/www/html/

# Buat folder upload & set permission
RUN mkdir -p /var/www/html/uploads/dokumen \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Konfigurasi Apache: izinkan .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/simpas.conf \
    && a2enconf simpas

# Set ServerName agar tidak ada warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
