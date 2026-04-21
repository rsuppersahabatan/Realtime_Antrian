FROM php:7.4-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules: rewrite + reverse proxy (HTTP & WebSocket) untuk Socket.IO
RUN a2enmod rewrite proxy proxy_http proxy_wstunnel

# Set document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Configure Apache to allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Reverse proxy /socket.io/ -> service nodejs:8085 (lihat docker/apache/socket-proxy.conf)
COPY docker/apache/socket-proxy.conf /etc/apache2/conf-available/socket-proxy.conf
RUN a2enconf socket-proxy

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
