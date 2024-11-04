FROM php:7.4.3-fpm-alpine3.11

# Устанавливаем зависимости
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    openssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apk del .build-deps

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Создаем директорию для приложения
WORKDIR /app

# Копируем composer.json
COPY ./docker/php/composer.json .

# Устанавливаем зависимости через Composer
RUN composer install --no-interaction --no-progress --no-suggest

# Copy the php config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy the application code
COPY . /app

VOLUME ["/app"]
