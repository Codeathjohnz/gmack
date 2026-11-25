# Gmack POS — CodeIgniter 4 on PHP 8.2 + Apache (for Dokploy)
FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev unzip git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" intl mysqli pdo_mysql zip gd \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# Serve the CodeIgniter public/ folder as the web root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
 && printf "upload_max_filesize=20M\npost_max_size=25M\nmemory_limit=256M\ndate.timezone=Asia/Manila\nexpose_php=Off\n" > /usr/local/etc/php/conf.d/gmack.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize \
 && mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar public/uploads/items public/uploads/qrcodes public/uploads/alarms \
 && chown -R www-data:www-data writable public/uploads

EXPOSE 80
