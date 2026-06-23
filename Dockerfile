FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    make

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-scripts --no-progress --no-autoloader

# Copy project files
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize --no-scripts

# Fix permissions
RUN chown -R 1000:1000 /var/www/html

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
