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

# ✅ Copy full project first (so artisan exists)
COPY . .

# ✅ Install dependencies (artisan exists now)
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Permissions (Apache user)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ✅ Add start script (migrate at runtime)
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]