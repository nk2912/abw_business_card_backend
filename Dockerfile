# ====== Base image with PHP + Apache ======
FROM php:8.2-apache

# Enable Apache rewrite for Laravel (public/index.php)
RUN a2enmod rewrite headers

# System deps + PHP extensions (pgsql required)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libpq-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip gd \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set Apache document root to Laravel /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Workdir
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Laravel optimizations (safe)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear  || true

# Permissions (Apache user)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Render uses $PORT - Apache normally listens on 80, but Render maps port -> container 80 OK.
# Expose 80 for local usage (Render ignores EXPOSE, but ok)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]