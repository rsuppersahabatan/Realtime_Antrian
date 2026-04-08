FROM php:7.4-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Configure Apache to allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
