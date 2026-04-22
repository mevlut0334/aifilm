FROM php:8.3-apache

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev \
    libxml2-dev zip unzip libzip-dev \
    nodejs npm

# PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip
RUN pecl install redis && docker-php-ext-enable redis
RUN a2enmod rewrite

# Copy custom PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/uploads.ini

# Apache configuration
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-interaction

# Build frontend assets
RUN npm ci --ignore-scripts
RUN npm run build

# Clean up npm to reduce image size
RUN npm cache clean --force && rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache public

EXPOSE 80

CMD php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    apache2-foreground
