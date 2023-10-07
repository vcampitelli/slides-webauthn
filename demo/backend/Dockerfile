FROM php:8.2-cli-alpine

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ["composer.json", "composer.lock", "./"]
RUN composer install --no-dev

COPY . .

CMD composer start
