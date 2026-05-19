FROM php:8.2-cli

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy aplikasi
COPY . /var/www/html/

# Set permission
RUN mkdir -p /var/www/html/uploads/dokumen \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

WORKDIR /var/www/html

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
