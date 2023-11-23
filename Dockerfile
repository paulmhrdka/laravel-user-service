# Use the official PHP image
FROM php:8.0-cli as builder

# Set working directory
WORKDIR /var/www/html/

# Install dependencies - Stage Build
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only the necessary files for composer dependencies
COPY composer.json composer.lock /var/www/html/
RUN composer install --no-scripts --no-autoloader

# Stage 2: Build the final image
FROM php:8.0-cli

# Set working directory
WORKDIR /var/www/html/

# Copy only necessary files from the builder stage
COPY --from=builder /var/www/html/vendor /var/www/html/vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Install dependencies - for app Final Stage
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql

# Copy the rest of the application files
COPY . /var/www/html/

# Generate autoload files
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Run Migration & Start Laravel application
CMD ["sh", "-c", "php artisan migrate && php artisan serve --host=0.0.0.0 --port=8000"] > 2&1
