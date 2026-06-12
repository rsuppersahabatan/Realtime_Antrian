FROM php:8.4-apache

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

# Copy application files (.dockerignore excludes .env, node_modules, dll)
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80