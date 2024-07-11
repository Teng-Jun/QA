# Use the official PHP image as the base image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/pixiegram

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Copy the existing application directory contents to the working directory
COPY . /var/www/pixiegram

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for the application
RUN chown -R www-data:www-data /var/www/pixiegram \
    && chmod -R 775 /var/www/pixiegram/storage \
    && chmod -R 775 /var/www/pixiegram/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
