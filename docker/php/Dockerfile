FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    nano \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy custom Apache configuration
COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]