# Use the official PHP image with Apache
FROM php:8.2-apache

# Install the mysqli extension (required for the app)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (optional, but recommended for PHP apps)
RUN a2enmod rewrite

# Set the working directory in the container
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80
