FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction



# RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]